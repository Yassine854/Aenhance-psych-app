import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { fetchConsultationJson } from '../resources/js/utils/consultationRequest.js';

function setup(t, handler, cookie = 'encrypted-cookie') {
  const dom = new JSDOM('<meta name="csrf-token" content="old-page-token">', { url: 'https://app.example/' });
  const originalFetch = globalThis.fetch;
  const originalDocument = globalThis.document;
  globalThis.document = dom.window.document;
  if (cookie) document.cookie = `XSRF-TOKEN=${encodeURIComponent(cookie)}; path=/`;
  globalThis.fetch = handler;
  t.after(() => {
    globalThis.fetch = originalFetch;
    globalThis.document = originalDocument;
    dom.window.close();
  });
}
const json = (body, status = 200) => new Response(JSON.stringify(body), { status, headers: { 'Content-Type': 'application/json' } });

test('JSON write preserves Content-Type and sends current cookie, not stale meta token', async (t) => {
  setup(t, async (url, options) => {
    assert.equal(url, '/appointments/3/session/join');
    assert.equal(options.headers.get('Content-Type'), 'application/json');
    assert.equal(options.headers.get('Accept'), 'application/json');
    assert.equal(options.headers.get('X-Requested-With'), 'XMLHttpRequest');
    assert.equal(options.headers.get('X-XSRF-TOKEN'), 'encrypted-cookie');
    assert.equal(options.headers.has('X-CSRF-TOKEN'), false);
    assert.equal(options.credentials, 'same-origin');
    return json({ session: { id: 3 } });
  });
  assert.deepEqual(await fetchConsultationJson('/appointments/3/session/join', {
    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: '{"roomId":"room"}',
  }), { session: { id: 3 } });
});

test('419 refresh retries once with newly issued cookie while retaining JSON headers', async (t) => {
  let writes = 0;
  let refreshes = 0;
  setup(t, async (url, options) => {
    if (url === '/sanctum/csrf-cookie') {
      refreshes++;
      document.cookie = 'XSRF-TOKEN=fresh-cookie; path=/';
      return new Response(null, { status: 204 });
    }
    writes++;
    assert.equal(options.headers.has('X-CSRF-TOKEN'), false);
    assert.equal(options.headers.get('Content-Type'), 'application/json');
    assert.equal(options.headers.get('X-XSRF-TOKEN'), writes === 1 ? 'encrypted-cookie' : 'fresh-cookie');
    return writes === 1 ? json({}, 419) : json({ ok: true });
  });
  assert.deepEqual(await fetchConsultationJson('/session-ratings', { method: 'post', headers: { 'Content-Type': 'application/json' } }), { ok: true });
  assert.equal(writes, 2);
  assert.equal(refreshes, 1);
});

test('fallback uses page token when no cookie is available', async (t) => {
  setup(t, async (url, options) => {
    assert.equal(options.headers.get('X-CSRF-TOKEN'), 'old-page-token');
    assert.equal(options.headers.has('X-XSRF-TOKEN'), false);
    return json({ ok: true });
  }, '');
  await fetchConsultationJson('/appointment-session-notes', { method: 'POST' });
});

test('persistent 419 reports a recoverable session error and does not loop', async (t) => {
  let calls = 0;
  setup(t, async () => { calls++; return json({}, calls === 2 ? 200 : 419); });
  await assert.rejects(fetchConsultationJson('/join', { method: 'POST' }), (error) => error.status === 419 && error.message.includes('Refresh this page'));
  assert.equal(calls, 3);
});

test('signaling 503 retains its server diagnostic and status', async (t) => {
  setup(t, async () => json({ message: 'Signaling authentication is not configured in Laravel.' }, 503));
  await assert.rejects(fetchConsultationJson('/appointments/3/signaling-token'), (error) => error.status === 503 && error.message.includes('Signaling authentication'));
});
