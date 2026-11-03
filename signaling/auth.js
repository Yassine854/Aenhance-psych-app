import { createHmac, timingSafeEqual } from 'node:crypto';

export function verifyJoinToken(token, secret, now = Math.floor(Date.now() / 1000)) {
  if (typeof token !== 'string' || token.length > 8192 || typeof secret !== 'string' || secret.length < 32) return null;
  const parts = token.split('.');
  if (parts.length !== 2 || !/^[a-zA-Z0-9_-]+$/.test(parts[0]) || !/^[a-f0-9]{64}$/.test(parts[1])) return null;
  const expected = createHmac('sha256', secret).update(parts[0]).digest();
  if (!timingSafeEqual(expected, Buffer.from(parts[1], 'hex'))) return null;
  try {
    const payload = JSON.parse(Buffer.from(parts[0], 'base64url').toString('utf8'));
    if (!Number.isInteger(payload.exp) || payload.exp <= now || payload.exp > now + 300) return null;
    if (typeof payload.roomId !== 'string' || !payload.roomId || payload.roomId.length > 128) return null;
    if (!['patient', 'psychologist'].includes(payload.role) || typeof payload.name !== 'string') return null;
    return payload;
  } catch {
    return null;
  }
}
