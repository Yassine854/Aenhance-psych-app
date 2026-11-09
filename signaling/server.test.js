import test from 'node:test';
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { once } from 'node:events';
import { createHmac } from 'node:crypto';
import WebSocket from '../node_modules/ws/wrapper.mjs';

test('room access is signed, isolated, and role restricted', { timeout: 15000 }, async (t) => {
  const secret = 'test-only-signaling-secret-32-characters';
  const child = spawn(process.execPath, ['signaling/server.js'], {
    env: { ...process.env, SIGNALING_AUTH_SECRET: secret, PORT: '0' },
    stdio: ['ignore', 'pipe', 'pipe'],
  });
  const sockets = [];
  t.after(() => {
    for (const socket of sockets) socket.terminate();
    child.kill();
  });
  const port = await new Promise((resolve, reject) => {
    let output = '';
    child.stdout.on('data', (chunk) => {
      output += chunk;
      const match = output.match(/running on port (\d+)/);
      if (match) resolve(Number(match[1]));
    });
    child.once('error', reject);
    child.once('exit', (code) => reject(new Error(`Signaling exited: ${code}`)));
  });
  async function connect() {
    const socket = new WebSocket(`ws://127.0.0.1:${port}`);
    sockets.push(socket);
    await once(socket, 'open');
    return socket;
  }
  function token(role, roomId = 'room-1') {
    const body = Buffer.from(JSON.stringify({ roomId, role, name: role, exp: Math.floor(Date.now() / 1000) + 300 })).toString('base64url');
    return `${body}.${createHmac('sha256', secret).update(body).digest('hex')}`;
  }
  async function sendAndReceive(sender, receiver, message) {
    const next = once(receiver, 'message');
    sender.send(JSON.stringify(message));
    return JSON.parse((await next)[0]);
  }
  const attacker = await connect();
  const closed = once(attacker, 'close');
  attacker.send(JSON.stringify({ type: 'join', roomId: 'room-1', role: 'psychologist' }));
  assert.equal((await closed)[0], 1008);

  const psychologist = await connect();
  // Client-supplied room and role cannot override signed claims.
  const joined = await sendAndReceive(psychologist, psychologist, { type: 'join', roomId: 'forged', role: 'patient', token: token('psychologist') });
  assert.equal(joined.roomId, 'room-1');
  let patient = await connect();
  const peerJoined = once(psychologist, 'message');
  await sendAndReceive(patient, patient, { type: 'join', token: token('patient') });
  assert.equal(JSON.parse((await peerJoined)[0]).type, 'peer-joined');

  patient.send(JSON.stringify({ type: 'session-ended' }));
  const chat = await sendAndReceive(patient, psychologist, { type: 'chat', payload: { text: 'marker' } });
  assert.equal(chat.type, 'chat');
  assert.equal((await sendAndReceive(psychologist, patient, { type: 'session-ended' })).type, 'session-ended');
  const offer = await sendAndReceive(psychologist, patient, { type: 'offer', sdp: { type: 'offer', sdp: 'test' } });
  assert.equal(offer.from.role, 'psychologist');

  // An abrupt signaling loss must not tell the other browser to close media.
  const duringOutage = [];
  const recordOutage = (raw) => duringOutage.push(JSON.parse(raw));
  psychologist.on('message', recordOutage);
  const patientClosed = once(patient, 'close');
  patient.terminate();
  await patientClosed;
  await new Promise((resolve) => setTimeout(resolve, 100));
  assert.equal(duringOutage.some((msg) => msg.type === 'peer-left'), false);
  patient = await connect();
  const rejoined = once(psychologist, 'message');
  const room = await sendAndReceive(patient, patient, { type: 'join', token: token('patient') });
  assert.equal(room.peerCount, 2);
  assert.equal(JSON.parse((await rejoined)[0]).type, 'peer-joined');
  assert.equal(duringOutage.some((msg) => msg.type === 'peer-left'), false);
  psychologist.off('message', recordOutage);
  assert.equal((await sendAndReceive(patient, psychologist, { type: 'restart-request' })).type, 'restart-request');

  // Duplicate tabs get a terminal close code rather than reconnecting forever.
  const replacement = await connect();
  const replaced = once(patient, 'close');
  const replacementJoined = once(psychologist, 'message');
  await sendAndReceive(replacement, replacement, { type: 'join', token: token('patient') });
  assert.equal((await replaced)[0], 4001);
  assert.equal(JSON.parse((await replacementJoined)[0]).type, 'peer-joined');
  patient = replacement;

  const outsider = await connect();
  await sendAndReceive(outsider, outsider, { type: 'join', token: token('patient', 'other-room') });
  outsider.send(JSON.stringify({ type: 'chat', payload: { text: 'wrong room' } }));
  const isolated = await sendAndReceive(patient, psychologist, { type: 'chat', payload: { text: 'right room' } });
  assert.equal(isolated.payload.text, 'right room');

  const limited = once(patient, 'close');
  for (let i = 0; i < 301; i++) patient.send(JSON.stringify({ type: 'unsupported' }));
  assert.equal((await limited)[0], 4029);
});
