import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { JSDOM } from 'jsdom';
import { parse, compileScript } from '@vue/compiler-sfc';

const dom = new JSDOM('<!doctype html><html><body></body></html>', { url: 'http://localhost' });
for (const key of ['window', 'document', 'Element', 'SVGElement', 'HTMLElement', 'Node', 'localStorage']) globalThis[key] = dom.window[key];
const { createApp, h, nextTick, reactive } = await import('vue');
const { createI18n } = await import('vue-i18n');
const uri = source => `data:text/javascript;base64,${Buffer.from(source).toString('base64')}`;
const stub = uri('export default { render() { return null } }');
const inertia = uri('export const Head={render(){return null}},Link={render(){return null}},router={reload(){}}');

for (const role of ['Patient', 'Psychologist']) {
  const source = readFileSync(new URL(`../resources/js/Pages/${role}/Notifications/Index.vue`, import.meta.url), 'utf8');
  const compiled = compileScript(parse(source).descriptor, { id: `notifications-${role}`, inlineTemplate: true }).content
    .replace(/from (["'])(vue|vue-i18n)\1/g, (_match, _quote, name) => `from '${import.meta.resolve(name)}'`)
    .replace("'@inertiajs/vue3'", `'${inertia}'`)
    .replace(/'@\/Components\/[^']+'/g, `'${stub}'`)
    .replace('@/utils/i18n.js', new URL('../resources/js/utils/i18n.js', import.meta.url).href);
  const { default: Page } = await import(uri(compiled));
  for (const locale of ['en', 'fr', 'ar']) {
    test(`${role} notification pagination replaces raw tokens and updates after changing pages (${locale})`, async () => {
      const messages = JSON.parse(readFileSync(new URL(`../resources/js/i18n/${locale}.json`, import.meta.url), 'utf8'));
      const raw = messages.notifications.showing;
      // Reproduce a deployed runtime returning the translation without interpolation.
      messages.notifications.showing = () => raw;
      localStorage.setItem('locale', locale);
      const state = reactive({ data: [{ id: 1, title: 'Test notification', is_read: true }], from: 1, to: 10, total: 24, links: [] });
      const host = document.createElement('div'); document.body.append(host);
      const app = createApp({ render: () => h(Page, { notifications: state }) });
      app.config.globalProperties.route = () => '/appointments';
      app.directive('safe-html', {});
      app.use(createI18n({ legacy: false, locale, messages: { [locale]: messages } }));
      app.mount(host);
      try {
        const footer = () => host.querySelector('.text-sm.text-gray-600');
        assert(footer().textContent.includes('1'));
        assert(footer().textContent.includes('10'));
        assert(footer().textContent.includes('24'));
        assert(!/\{(?:start|end|total)\}/.test(footer().textContent));
        if (locale === 'en') assert.equal(footer().textContent.trim(), 'Showing 1-10 of 24');
        state.from = 11; state.to = 20;
        await nextTick();
        assert(footer().textContent.includes('11'));
        assert(footer().textContent.includes('20'));
        state.from = null; state.to = null; state.total = 0;
        await nextTick();
        assert(!footer().textContent.includes('null'));
        assert(footer().textContent.includes('0'));
      } finally { app.unmount(); host.remove(); }
    });
  }
}
