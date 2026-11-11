import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import { parse } from '@babel/parser';
import { callInteractionMessage, readCallInteraction } from '../resources/js/utils/callInteractions.js';

const script = readFileSync(new URL('../resources/js/Components/VideoCall/VideoCallPanel.vue', import.meta.url), 'utf8')
  .split('<script setup>')[1].split('</script>')[0];
const functions = new Set(['scheduleSignalingReconnect', 'handleSignalingRequestError', 'scheduleMediaRecovery', 'connectSignaling', 'makeOffer', 'hangUp', 'fetchCallJson', 'refreshIceConfiguration', 'handleConnectionWake', 'createPeerConnection']);
const recoveryCode = parse(script, { sourceType: 'module' }).program.body
  .filter((node) => node.type === 'FunctionDeclaration' && functions.has(node.id.name))
  .map((node) => script.slice(node.start, node.end)).join('\n').replaceAll('import.meta.env', 'env');

function fixture() {
  const timers = new Map();
  const sockets = [];
  const peer = { connectionState: 'connected', close() { throw new Error('Active media must survive signaling loss'); } };
  let tokenCount = 0;
  let timerId = 0;
  class Socket {
    static OPEN = 1;
    static CONNECTING = 0;
    static CLOSING = 2;
    static CLOSED = 3;
    constructor() { this.readyState = 1; sockets.push(this); }
    close() { this.readyState = 3; this.onclose?.({ code: 1000 }); }
  }
  const context = vm.createContext({
    env: {}, props: { roomId: 'room', appointmentId: 1, signalingUrl: 'wss://test', role: 'patient' },
    leavingCall: false, signalingBlocked: false, reconnectTimer: null, reconnectAttempt: 0,
    authorizationRetryCount: 0,
    mediaRecoveryTimer: null, ws: null, pc: peer, pendingMediaState: null,
    signalingConnecting: false, makingOffer: false, removeCallListeners() {},
    localOfferSentAt: 0,
    turnCredentialsExpiresAt: 0, iceConfigurationRefreshedAt: 0,
    AbortController, document: { visibilityState: 'visible' },
    error: { value: '' }, remoteHandRaised: { value: false }, handRaised: { value: false },
    callInteractionMessage, readCallInteraction, console: { warn() {} }, WebSocket: Socket,
    setTimeout(fn) { const id = ++timerId; timers.set(id, fn); return id; },
    clearTimeout(id) { timers.delete(id); }, clearInterval() {},
    fetchJson: async () => ({ token: `fresh-${++tokenCount}` }),
    wsSend() {}, sendMediaState() {}, syncSessionBackend() {},
    setConnectionState(state) { context.state = state; },
    leaveSessionBackend() {}, resetPeerConnection() { context.pc = null; },
    stopSpeakingDetection() {}, localStream: null, callTimer: null,
  });
  vm.runInContext(recoveryCode, context);
  return { context, timers, sockets, peer, tokens: () => tokenCount };
}

async function join(context, sockets) {
  await context.connectSignaling();
  await sockets.at(-1).onmessage({ data: JSON.stringify({ type: 'joined', peers: [] }) });
}

test('incoming reactions reach the call overlay after joining', async () => {
  const { context, sockets } = fixture();
  const received = [];
  context.interactionsRef = { value: { receiveReaction: (...args) => received.push(args) } };
  context.remoteDisplayName = { value: 'Other participant' };
  await join(context, sockets);
  await sockets[0].onmessage({ data: JSON.stringify({ type: 'reaction', reaction: 'love' }) });
  assert.deepEqual(received, [['love', 'Other participant']]);
});

test('legacy chat relay delivers reactions and hand updates without creating chat messages', async () => {
  const { context, sockets } = fixture();
  const received = [];
  context.interactionsRef = { value: { receiveReaction: (...args) => received.push(args) } };
  context.remoteDisplayName = { value: 'Other participant' };
  context.sessionActive = { value: false };
  context.chatRef = { value: { receiveMessage() { throw new Error('Interactions must not appear in chat'); } } };
  await join(context, sockets);
  for (const message of [callInteractionMessage('reaction', 'happy'), callInteractionMessage('hand', true)]) {
    await sockets[0].onmessage({ data: JSON.stringify(message) });
  }
  assert.deepEqual(received, [['happy', 'Other participant']]);
  assert.equal(context.remoteHandRaised.value, true);
  await sockets[0].onmessage({ data: JSON.stringify(callInteractionMessage('hand', false)) });
  assert.equal(context.remoteHandRaised.value, false);
  await sockets[0].onmessage({ data: JSON.stringify(callInteractionMessage('hand', 'invalid')) });
  assert.equal(context.remoteHandRaised.value, false);
});

test('a peer joining receives the current raised hand through the legacy relay', async () => {
  const { context, sockets } = fixture();
  const sent = [];
  context.wsSend = message => sent.push(message);
  context.setRemoteIdentity = () => {};
  context.handRaised.value = true;
  await join(context, sockets);
  await sockets[0].onmessage({ data: JSON.stringify({ type: 'peer-joined', role: 'psychologist' }) });
  assert(sent.some(message => readCallInteraction(message)?.raised === true));
});

test('signaling loss preserves media and reconnects with fresh authorization', async () => {
  const { context, timers, sockets, peer, tokens } = fixture();
  await join(context, sockets);
  sockets[0].readyState = 3;
  sockets[0].onclose({ code: 1006 });
  assert.equal(context.pc, peer);
  assert.equal(context.error.value, '');
  assert.equal(context.state, undefined);
  assert.equal(timers.size, 1);
  const retry = timers.values().next().value;
  timers.clear();
  retry();
  await new Promise((resolve) => setImmediate(resolve));
  assert.equal(sockets.length, 2);
  assert.equal(tokens(), 2);
  await sockets[1].onmessage({ data: JSON.stringify({ type: 'joined', peers: [] }) });
  // Events from replaced sockets must not create extra retry loops.
  sockets[0].onclose({ code: 1006 });
  assert.equal(timers.size, 0);
});

test('duplicate tabs and rejected authorization do not reconnect forever', async () => {
  for (const code of [1008, 4001]) {
    const { context, sockets, timers, peer } = fixture();
    await join(context, sockets);
    context.authorizationRetryCount = 1;
    sockets[0].onclose({ code });
    assert.equal(context.signalingBlocked, true);
    assert.equal(timers.size, 0);
    assert.equal(context.pc, peer);
    assert.notEqual(context.error.value, '');
  }
});

test('hangup cancels pending recovery and prevents rejoining', async () => {
  const { context, sockets, timers } = fixture();
  await join(context, sockets);
  sockets[0].readyState = 3;
  sockets[0].onclose({ code: 1006 });
  context.scheduleMediaRecovery();
  assert.equal(timers.size, 2);
  context.hangUp();
  assert.equal(timers.size, 0);
  await context.connectSignaling();
  assert.equal(sockets.length, 1);
});

test('media recovery requests an ICE restart and stops after media recovers', async () => {
  const { context, timers, peer, sockets } = fixture();
  const sent = [];
  context.wsSend = (message) => sent.push(message.type);
  await join(context, sockets);
  peer.connectionState = 'disconnected';
  context.scheduleMediaRecovery();
  const recover = timers.values().next().value;
  timers.clear();
  await recover();
  assert.deepEqual(sent, ['restart-request']);
  assert.equal(timers.size, 1);
  peer.connectionState = 'connected';
  const checkRecovered = timers.values().next().value;
  timers.clear();
  await checkRecovered();
  assert.equal(timers.size, 0);
});

test('stalled room join closes only signaling and schedules one retry', async () => {
  const { context, timers, sockets, peer } = fixture();
  await context.connectSignaling();
  assert.equal(timers.size, 1);
  const watchdog = timers.values().next().value;
  watchdog();
  assert.equal(sockets[0].readyState, 3);
  assert.equal(context.pc, peer);
  assert.equal(timers.size, 1);
  context.hangUp();
  assert.equal(timers.size, 0);
});

test('credential requests time out rather than blocking connection recovery', async () => {
  const { context, timers } = fixture();
  context.fetchJson = (_url, { signal }) => new Promise((_resolve, reject) => {
    signal.addEventListener('abort', () => reject(new Error('Aborted')));
  });
  const connecting = context.connectSignaling();
  const rejected = assert.rejects(connecting, /Aborted/);
  timers.values().next().value();
  await rejected;
  assert.equal(context.signalingConnecting, false);
  assert.equal(timers.size, 0);
});

test('offer application finishes before later ICE messages run', async () => {
  const { context, sockets, peer } = fixture();
  await join(context, sockets);
  const events = [];
  let finishDescription;
  peer.setRemoteDescription = () => new Promise((resolve) => {
    events.push('description-start');
    finishDescription = () => { events.push('description-end'); resolve(); };
  });
  peer.createAnswer = async () => ({ type: 'answer', sdp: 'answer' });
  peer.setLocalDescription = async (sdp) => { peer.localDescription = sdp; };
  peer.getSenders = () => [];
  context.createPeerConnection = async () => peer;
  context.turnCredentialsExpiresAt = Date.now() + 3600000;
  context.setRemoteIdentity = () => {};
  context.RTCSessionDescription = class { constructor(sdp) { Object.assign(this, sdp); } };
  context.flushPendingIce = async () => {};
  context.addIceCandidateBuffered = async () => { events.push('ice'); };
  const offered = sockets[0].onmessage({ data: JSON.stringify({ type: 'offer', sdp: { type: 'offer', sdp: 'offer' } }) });
  const ice = sockets[0].onmessage({ data: JSON.stringify({ type: 'ice', candidate: {} }) });
  await new Promise((resolve) => setImmediate(resolve));
  assert.deepEqual(events, ['description-start']);
  finishDescription();
  await Promise.all([offered, ice]);
  assert.deepEqual(events, ['description-start', 'description-end', 'ice']);
});

test('concurrent negotiation triggers create only one local offer', async () => {
  const { context, sockets, peer } = fixture();
  await join(context, sockets);
  peer.signalingState = 'stable';
  context.createPeerConnection = async () => peer;
  let offers = 0;
  peer.createOffer = async () => { offers++; return { type: 'offer', sdp: 'offer' }; };
  peer.setLocalDescription = async (sdp) => { peer.localDescription = sdp; };
  await Promise.all([context.makeOffer(), context.makeOffer()]);
  assert.equal(offers, 1);
  assert.equal(context.makingOffer, false);
});

test('TURN refresh preserves a working relay during an endpoint outage and throttles retries', async () => {
  const { context, peer } = fixture();
  let fetched = 0;
  let configured = 0;
  context.getRtcConfig = async () => { fetched++; return { iceServers: [{ urls: 'stun:test' }] }; };
  peer.setConfiguration = () => { configured++; };
  assert.equal(await context.refreshIceConfiguration(peer), false);
  await context.refreshIceConfiguration(peer);
  assert.equal(fetched, 1);
  assert.equal(configured, 0);
  context.iceConfigurationRefreshedAt = 0;
  context.getRtcConfig = async () => ({ iceServers: [{ urls: 'turn:test', credential: 'temporary' }] });
  assert.equal(await context.refreshIceConfiguration(peer), true);
  assert.equal(configured, 1);
});

test('ICE recovery does not repeatedly roll back an offer while its answer is in flight', async () => {
  const { context, sockets, peer } = fixture();
  await join(context, sockets);
  context.createPeerConnection = async () => peer;
  context.turnCredentialsExpiresAt = Date.now() + 3600000;
  context.localOfferSentAt = Date.now();
  peer.signalingState = 'have-local-offer';
  let rollbacks = 0;
  peer.setLocalDescription = async () => { rollbacks++; };
  await context.makeOffer(true);
  assert.equal(rollbacks, 0);
});

test('joining without a camera reserves a sender and renders streamless remote video', async () => {
  const { context } = fixture();
  const stream = { tracks: [], getTracks() { return this.tracks; }, addTrack(track) { this.tracks.push(track); }, getVideoTracks() { return this.tracks.filter((track) => track.kind === 'video'); } };
  const cameraSender = { track: null };
  let reserved;
  context.pc = null;
  context.localVideoSender = null;
  context.localStream = stream;
  context.setIceModeLabel = () => {};
  context.getRtcConfig = async () => ({ iceServers: [] });
  context.RTCPeerConnection = class {
    addTransceiver(kind, options) { reserved = { kind, options }; return { sender: cameraSender }; }
  };
  context.remoteVideo = { value: { srcObject: stream } };
  context.remoteVideoAvailable = { value: false };
  context.updateRemoteVideoRenderable = () => {};
  const peer = await context.createPeerConnection();
  assert.equal(reserved.kind, 'video');
  assert.equal(reserved.options.direction, 'sendrecv');
  assert.equal(reserved.options.streams[0], stream);
  assert.equal(context.localVideoSender, cameraSender);
  const videoTrack = { kind: 'video', enabled: true, muted: false, readyState: 'live' };
  peer.ontrack({ streams: [], track: videoTrack });
  assert.equal(context.remoteVideo.value.srcObject, stream);
  assert.equal(stream.getVideoTracks()[0], videoTrack);
  assert.equal(context.remoteVideoAvailable.value, true);
});

test('returning online bypasses backoff without duplicating open connections', async () => {
  const { context, sockets, timers } = fixture();
  await join(context, sockets);
  context.handleConnectionWake();
  assert.equal(sockets.length, 1);
  sockets[0].readyState = 3;
  sockets[0].onclose({ code: 1006 });
  assert.equal(timers.size, 1);
  context.handleConnectionWake();
  await new Promise((resolve) => setImmediate(resolve));
  assert.equal(sockets.length, 2);
  assert.equal(context.reconnectTimer, null);
  context.hangUp();
  assert.equal(timers.size, 0);
});

test('an authorization rejection fetches a fresh token once then stops on persistent rejection', async () => {
  const { context, sockets, timers, tokens, peer } = fixture();
  await join(context, sockets);
  sockets[0].readyState = 3;
  sockets[0].onclose({ code: 1008, reason: 'Invalid or expired room authorization' });
  assert.equal(context.signalingBlocked, false);
  assert.equal(context.error.value, '');
  const retry = timers.values().next().value;
  timers.clear();
  retry();
  await new Promise((resolve) => setImmediate(resolve));
  assert.equal(tokens(), 2);
  sockets[1].readyState = 3;
  sockets[1].onclose({ code: 1008, reason: 'Invalid or expired room authorization' });
  assert.equal(context.signalingBlocked, true);
  assert.equal(timers.size, 0);
  assert.equal(context.pc, peer);
});

test('join timeout and rate limiting are not reported as rejected room authorization', async () => {
  for (const event of [
    { code: 4008 }, { code: 1008, reason: 'Authentication required' },
    { code: 4029 }, { code: 1008, reason: 'Too many messages' },
  ]) {
    const { context, sockets, timers } = fixture();
    await join(context, sockets);
    sockets[0].readyState = 3;
    sockets[0].onclose(event);
    assert.equal(context.authorizationRetryCount, 0);
    if (event.code === 4029 || event.reason === 'Too many messages') {
      assert.match(context.error.value, /Too many call connection updates/);
      assert.equal(timers.size, 0);
    } else {
      assert.equal(context.error.value, '');
      assert.equal(timers.size, 1);
    }
  }
});
