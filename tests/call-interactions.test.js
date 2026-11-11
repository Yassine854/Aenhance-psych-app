import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { JSDOM } from 'jsdom';
import { parse, compileScript } from '@vue/compiler-sfc';
import { formatCallMessage } from '../resources/js/utils/callInteractions.js';

const dom = new JSDOM('<!doctype html><html><body></body></html>');
for (const key of ['window', 'document', 'Element', 'SVGElement', 'HTMLElement', 'Node']) {
  globalThis[key] = dom.window[key];
}
const { createApp, h, reactive, nextTick } = await import('vue');
const { createI18n } = await import('vue-i18n');
const source = readFileSync(new URL('../resources/js/Components/VideoCall/VideoCallInteractions.vue', import.meta.url), 'utf8');
const { descriptor } = parse(source);
const compiled = compileScript(descriptor, { id: 'call-interactions-test', inlineTemplate: true }).content
  .replace(/from (["'])(vue|vue-i18n)\1/g, (_match, _quote, name) => `from '${import.meta.resolve(name)}'`)
  .replace('../../utils/callInteractions.js', new URL('../resources/js/utils/callInteractions.js', import.meta.url).href);
const interactionsUrl = `data:text/javascript;base64,${Buffer.from(compiled).toString('base64')}`;
const { default: Interactions } = await import(interactionsUrl);
const toolbarSource = readFileSync(new URL('../resources/js/Components/VideoCall/VideoCallToolbar.vue', import.meta.url), 'utf8');
const toolbarCompiled = compileScript(parse(toolbarSource).descriptor, { id: 'call-toolbar-test', inlineTemplate: true }).content
  .replace(/from (["'])(vue|vue-i18n)\1/g, (_match, _quote, name) => `from '${import.meta.resolve(name)}'`)
  .replace('./VideoCallInteractions.vue', interactionsUrl);
const { default: Toolbar } = await import(`data:text/javascript;base64,${Buffer.from(toolbarCompiled).toString('base64')}`);
const messages = JSON.parse(readFileSync(new URL('../resources/js/i18n/en.json', import.meta.url), 'utf8'));

test('reaction controls send, display, validate, and throttle reactions; hand toggles stay accessible', async () => {
  const sent = [];
  const state = reactive({ handRaised: false, reactionsEnabled: true });
  let component;
  const host = document.createElement('div');
  document.body.append(host);
  const app = createApp({ render: () => h(Interactions, {
    ...state, displayName: 'Amine', ref: value => { component = value; },
    sendReaction: id => { sent.push(id); return true; },
    onToggleHand: () => { state.handRaised = !state.handRaised; },
  }) });
  app.use(createI18n({ legacy: false, locale: 'en', messages: { en: messages } }));
  app.mount(host);
  try {
    const button = label => host.querySelector(`button[aria-label="${label}"]`);
    button('Reactions').click();
    await nextTick();
    button('Happy').click();
    await nextTick();
    assert.deepEqual(sent, ['happy']);
    assert(host.textContent.includes('Amine'));
    assert.equal(button('Love').disabled, true);
    button('Love').click();
    assert.equal(sent.length, 1);
    component.receiveReaction('love', 'Other person');
    component.receiveReaction('<script>', 'Invalid reaction');
    await nextTick();
    assert(host.textContent.includes('Other person'));
    assert(!host.textContent.includes('Invalid reaction'));
    button('Raise hand').click();
    await nextTick();
    assert.equal(button('Lower hand').getAttribute('aria-pressed'), 'true');
    button('Lower hand').click();
    await nextTick();
    assert.equal(button('Raise hand').getAttribute('aria-pressed'), 'false');
    document.dispatchEvent(new dom.window.KeyboardEvent('keydown', { key: 'Escape' }));
    await nextTick();
    assert.equal(button('Reactions').getAttribute('aria-expanded'), 'false');
    state.reactionsEnabled = false;
    await nextTick();
    assert.equal(button('Reactions').disabled, true);
  } finally {
    app.unmount();
    host.remove();
  }
});

test('call messages replace names in all locales, including raw translation strings', () => {
  for (const locale of ['en', 'fr', 'ar']) {
    const data = JSON.parse(readFileSync(new URL(`../resources/js/i18n/${locale}.json`, import.meta.url), 'utf8'));
    const i18n = createI18n({ legacy: false, locale, messages: { [locale]: data } });
    const key = 'videoCall.interactions.handRaised';
    const actual = formatCallMessage(i18n.global.t, key, { name: 'Amine' });
    assert(actual.includes('Amine'));
    assert(!actual.includes('{name}'));
    const raw = formatCallMessage(() => data.videoCall.interactions.handRaised, key, { name: 'Amine' });
    assert.equal(raw, actual);
  }
});

test('compact toolbar auto-hides, pins open options, and keeps reactions and active badges visible', async t => {
  t.mock.timers.enable({ apis: ['setTimeout'] });
  const state = reactive({ connected: true, handRaised: false, unreadCount: 0, chatOpen: false, fullscreen: false });
  const sent = [];
  let toolbar;
  const host = document.createElement('div');
  document.body.append(host);
  const app = createApp({ render: () => h(Toolbar, {
    ...state, displayName: 'Amine', viewMode: 'cover',
    ref: value => { toolbar = value; },
    sendReaction: id => { sent.push(id); return true; },
    onToggleHand: () => { state.handRaised = !state.handRaised; },
    onChat: () => { state.chatOpen = true; state.unreadCount = 0; },
    onFullscreen: () => { state.fullscreen = !state.fullscreen; },
  }, { default: () => ['Mic', 'Camera', 'Hang up'].map(label => h('button', label)) }) });
  app.use(createI18n({ legacy: false, locale: 'en', messages: { en: messages } }));
  app.mount(host);
  try {
    await nextTick();
    const more = host.querySelector('.more-btn');
    const bar = more.parentElement;
    const menu = host.querySelector('.call-options');
    assert.equal(bar.querySelectorAll('button').length, 5);
    assert.equal(host.querySelectorAll('.floating-action').length, 2);
    assert(!menu.textContent.includes('Copy room'));
    assert(menu.textContent.includes('Fit'));
    host.querySelector('.fullscreen-btn').click();
    await nextTick();
    assert.equal(state.fullscreen, true);
    assert.equal(host.querySelector('.fullscreen-btn').getAttribute('aria-label'), messages.videoCall.minimize);
    host.querySelector('.fullscreen-btn').blur();
    assert.equal(menu.style.display, 'none');
    t.mock.timers.tick(3600);
    await nextTick();
    assert.equal(bar.style.display, 'none');
    toolbar.revealControls();
    await nextTick();
    assert.notEqual(bar.style.display, 'none');
    more.click();
    await nextTick();
    assert.notEqual(menu.style.display, 'none');
    t.mock.timers.tick(10000);
    await nextTick();
    assert.notEqual(bar.style.display, 'none');
    menu.querySelector('[aria-label="Happy"]').click();
    await nextTick();
    assert.deepEqual(sent, ['happy']);
    menu.dispatchEvent(new dom.window.KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
    await nextTick();
    assert.equal(menu.style.display, 'none');
    assert.equal(document.activeElement, more);
    toolbar.receiveReaction('love', 'Other person');
    await nextTick();
    const bubble = [...host.querySelectorAll('.reaction-float')].find(el => el.textContent.includes('Other person'));
    assert(bubble);
    assert.equal(bubble.closest('.call-options'), null);
    state.handRaised = true;
    state.unreadCount = 2;
    await nextTick();
    const lower = host.querySelector('button[title="Lower hand"]');
    assert(lower);
    assert.equal(lower.closest('.call-options'), null);
    lower.click();
    await nextTick();
    assert.equal(state.handRaised, false);
    host.querySelector('button[aria-label="Open chat"]').click();
    await nextTick();
    assert.equal(state.chatOpen, true);
    assert.equal(state.unreadCount, 0);
  } finally { app.unmount(); host.remove(); }
});
