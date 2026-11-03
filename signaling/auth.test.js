import test from 'node:test';
import assert from 'node:assert/strict';
import { createHmac } from 'node:crypto';
import { verifyJoinToken } from './auth.js';

const secret = 'test-only-signaling-secret-32-characters';
function sign(payload, key = secret) {
  const body = Buffer.from(JSON.stringify(payload)).toString('base64url');
  return `${body}.${createHmac('sha256', key).update(body).digest('hex')}`;
}
const claims = { roomId: 'test-room', role: 'patient', name: 'Patient', exp: 1300 };

test('valid credentials preserve signed room and role', () => {
  assert.deepEqual(verifyJoinToken(sign(claims), secret, 1000), claims);
});
test('reject expired, future, malformed and forged credentials', () => {
  for (const token of [
    undefined, '', 'invalid', 'x.'.repeat(9000),
    sign({ ...claims, exp: 1000 }), sign({ ...claims, exp: 1301 }),
    sign({ ...claims, role: 'admin' }), sign({ ...claims, roomId: '' }),
    sign(claims, 'wrong-key'), sign(claims).replace(/.$/, 'z'),
  ]) assert.equal(verifyJoinToken(token, secret, 1000), null);
  assert.equal(verifyJoinToken(sign(claims), '', 1000), null);
});
