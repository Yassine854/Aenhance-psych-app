import test from 'node:test';
import assert from 'node:assert/strict';
import { callAudioConstraints, callVideoConstraints, configureCallVideoSender } from '../resources/js/utils/callMedia.js';

test('capture requests speech processing and bounded camera settings without requiring HD', () => {
  const audio = callAudioConstraints();
  assert.equal(audio.echoCancellation.ideal, true);
  assert.equal(audio.noiseSuppression.ideal, true);
  assert.equal(audio.autoGainControl.ideal, true);
  const video = callVideoConstraints();
  assert.equal(video.width.min, undefined);
  assert.equal(video.width.exact, undefined);
  assert.equal(video.frameRate.max, 30);
});

test('video limits preserve negotiated encoding identity and permit lower bitrates', async () => {
  const parameters = { transactionId: 'browser-id', encodings: [{ rid: 'main', active: true }] };
  let applied;
  const sender = { track: { kind: 'video' }, getParameters: () => parameters, setParameters: async (value) => { applied = value; } };
  assert.equal(await configureCallVideoSender(sender), true);
  assert.equal(applied.transactionId, 'browser-id');
  assert.equal(applied.encodings[0].rid, 'main');
  assert.equal(applied.encodings[0].active, true);
  assert.equal(applied.encodings[0].maxBitrate, 1200000);
  assert.equal(applied.encodings[0].minBitrate, undefined);
});

test('unsupported video tuning does not break calls or modify audio', async () => {
  let writes = 0;
  assert.equal(await configureCallVideoSender({ track: { kind: 'audio' }, setParameters: async () => { writes++; } }), false);
  assert.equal(writes, 0);
  const unsupported = { track: { kind: 'video' }, getParameters: () => ({ encodings: [{}] }), setParameters: async () => { throw new Error('Unsupported'); } };
  assert.equal(await configureCallVideoSender(unsupported), false);
});
