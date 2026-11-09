import { createHmac, timingSafeEqual } from 'node:crypto';

export function verifyJoinToken(token, secret, now = Math.floor(Date.now() / 1000)) {
  return inspectJoinToken(token, secret, now).identity;
}

export function inspectJoinToken(token, secret, now = Math.floor(Date.now() / 1000)) {
  const reject = (reason) => ({ identity: null, reason });
  if (typeof secret !== 'string' || secret.length < 32) return reject('server_configuration');
  if (typeof token !== 'string' || token.length > 8192) return reject('malformed_token');
  const parts = token.split('.');
  if (parts.length !== 2 || !/^[a-zA-Z0-9_-]+$/.test(parts[0]) || !/^[a-f0-9]{64}$/.test(parts[1])) return reject('malformed_token');
  const expected = createHmac('sha256', secret).update(parts[0]).digest();
  if (!timingSafeEqual(expected, Buffer.from(parts[1], 'hex'))) return reject('signature_mismatch');
  try {
    const payload = JSON.parse(Buffer.from(parts[0], 'base64url').toString('utf8'));
    if (!payload || typeof payload !== 'object' || !Number.isInteger(payload.exp)) return reject('invalid_claims');
    if (payload.exp <= now) return reject('expired_token');
    // Laravel issues five-minute tokens. Allow bounded clock skew when Node
    // is slightly behind Laravel, while continuing to reject expired tokens.
    if (payload.exp > now + 330) return reject('clock_skew');
    if (typeof payload.roomId !== 'string' || !payload.roomId || payload.roomId.length > 128) return reject('invalid_claims');
    if (!['patient', 'psychologist'].includes(payload.role) || typeof payload.name !== 'string') return reject('invalid_claims');
    return { identity: payload, reason: null };
  } catch {
    return reject('invalid_claims');
  }
}
