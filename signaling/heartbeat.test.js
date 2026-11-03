import test from 'node:test';
import assert from 'node:assert/strict';
import { EventEmitter } from 'node:events';
import { startHeartbeat } from './heartbeat.js';

test('heartbeat preserves responsive sockets and terminates unresponsive sockets', { timeout: 3000 }, async () => {
  const wss = new EventEmitter();
  const responsive = new EventEmitter();
  const unresponsive = new EventEmitter();
  wss.clients = new Set([responsive, unresponsive]);
  let pings = 0;
  responsive.ping = () => { pings++; responsive.emit('pong'); };
  responsive.terminate = () => assert.fail('Responsive socket was terminated');
  unresponsive.ping = () => {};
  const terminated = new Promise((resolve) => { unresponsive.terminate = resolve; });
  startHeartbeat(wss, 20);
  wss.emit('connection', responsive);
  wss.emit('connection', unresponsive);
  try {
    await terminated;
    assert.ok(pings >= 2);
  } finally {
    wss.emit('close');
  }
});
