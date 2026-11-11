import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, writeFileSync } from 'node:fs';
import { JSDOM } from 'jsdom';
import { parse, compileScript } from '@vue/compiler-sfc';

const dom = new JSDOM('<!doctype html><html><body></body></html>', { url: 'http://localhost' });
for (const key of ['window', 'document', 'Document', 'Element', 'SVGElement', 'HTMLElement', 'Node', 'localStorage']) globalThis[key] = dom.window[key];
globalThis.requestAnimationFrame = callback => setTimeout(callback, 16);
globalThis.getComputedStyle = dom.window.getComputedStyle;
const { createApp, h, nextTick, reactive } = await import('vue');
const { createI18n } = await import('vue-i18n');
const { default: safeHtml } = await import('../resources/js/directives/safeHtml.js');
const uri = code => `data:text/javascript;base64,${Buffer.from(code).toString('base64')}`;
const stub = uri('export default { render() { return null } }');
const inertia = uri(`import {h} from '${import.meta.resolve('vue')}';
 export const Head={render(){return null}};
 export const Link={props:['href'],setup(props,{slots}){return ()=>h('a',{href:props.href},slots.default?.())}};
 export const router={get(...args){globalThis.appointmentTest.calls.push(args)},post(){},patch(){}};
 export function usePage(){return {props:{auth:{user:{role:'PSYCHOLOGIST'}},flash:{}}}};`);
const swal = uri('export default {fire: async()=>({isConfirmed:false})}');
const source = readFileSync(new URL('../resources/js/Pages/Psychologist/Appointments/Index.vue', import.meta.url), 'utf8');
const compiled = compileScript(parse(source).descriptor, { id: 'psychologist-appointments-test', inlineTemplate: true }).content
  .replace(/from (["'])(vue|vue-i18n)\1/g, (_match, _quote, name) => `from '${import.meta.resolve(name)}'`)
  .replace("'@inertiajs/vue3'", `'${inertia}'`)
  .replace(/'@\/Components\/[^']+'/g, `'${stub}'`)
  .replace("'sweetalert2'", `'${swal}'`);
const { default: Page } = await import(uri(compiled));
const messages = JSON.parse(readFileSync(new URL('../resources/js/i18n/en.json', import.meta.url), 'utf8'));

test('appointment overview preserves actions and beneficiaries, filters across pages, and resets empty results', async t => {
  t.mock.timers.enable({ apis: ['setTimeout'] });
  globalThis.appointmentTest = { calls: [] };
  globalThis.route = (name, id) => `/${name}${id ? `/${id}` : ''}`;
  const appointment = (id, status, extra = {}) => ({ id, status, patient: { id: 20, name: 'Amine Patient' }, scheduled_start: '2026-12-01T10:00:00Z', scheduled_end: '2026-12-01T11:00:00Z', can_cancel: true, ...extra });
  const state = reactive({
    appointments: { data: [appointment(1, 'confirmed', { session_room_id: 'room' }), appointment(2, 'completed'), appointment(3, 'pending', { booking_for: 'other', beneficiary: { full_name: 'Sara Beneficiary', relationship_to_patient: 'sister' } })], total: 32, from: 1, to: 3, last_page: 3, links: [{ label: '&laquo; Previous', url: null }, { label: '1', url: '/page/1', active: true }, { label: '2', url: '/page/2' }] },
    summary: { total: 32, confirmed: 20, pending: 10, completed: 2 }, filters: {},
  });
  const host = document.createElement('div'); document.body.append(host);
  const app = createApp({ render: () => h(Page, state) });
  app.use(createI18n({ legacy: false, locale: 'en', messages: { en: messages } }));
  app.config.globalProperties.route = globalThis.route;
  app.directive('safe-html', safeHtml);
  app.mount(host);
  try {
    await nextTick(); await nextTick();
    assert.equal(appointmentTest.calls.length, 0);
    assert.equal(host.querySelectorAll('article').length, 3);
    if (process.env.APPOINTMENT_PREVIEW) {
      const manifest = JSON.parse(readFileSync(new URL('../public/build/manifest.json', import.meta.url), 'utf8'));
      const css = [...(manifest['resources/js/app.js'].css || []), ...(manifest['resources/js/Pages/Psychologist/Appointments/Index.vue'].css || [])];
      const pageCss = (manifest['resources/js/Pages/Psychologist/Appointments/Index.vue'].css || []).map(file => readFileSync(new URL(`../public/build/${file}`, import.meta.url), 'utf8').replace(/\[data-v-[^\]]+\]/g, '')).join('');
      writeFileSync(process.env.APPOINTMENT_PREVIEW, `<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">${css.map(file => `<link rel="stylesheet" href="../public/build/${file}">`).join('')}<style>${pageCss}</style></head><body>${host.innerHTML}</body></html>`);
    }
    assert(host.textContent.includes('Sara Beneficiary'));
    assert(host.querySelector('a[href="/appointments.video_call.show/1"]'));
    assert([...host.querySelectorAll('button')].some(button => button.textContent.trim() === 'Notes'));
    assert(!host.querySelector('a[href="#"]'));
    const filters = host.querySelector('[role="group"]');
    [...filters.querySelectorAll('button')].find(button => button.textContent === 'Confirmed').click();
    assert.equal(appointmentTest.calls.at(-1)[1].status_filter, 'confirmed');
    assert.equal(appointmentTest.calls.at(-1)[1].page, 1);
    assert(appointmentTest.calls.at(-1)[2].only.includes('summary'));
    state.filters = { status_filter: 'confirmed', search_field: 'patient', search_query: 'Nobody' };
    state.appointments = { data: [], total: 0, links: [] };
    await nextTick(); await nextTick();
    t.mock.timers.tick(500);
    assert.equal(appointmentTest.calls.length, 1, 'Hydrating server filters must not send duplicate requests');
    assert(host.textContent.includes('No matching appointments'));
    [...host.querySelectorAll('button')].find(button => button.textContent === 'Reset filters').click();
    await nextTick(); await nextTick();
    assert.equal(appointmentTest.calls.length, 2);
    assert.equal(appointmentTest.calls.at(-1)[1].status_filter, undefined);
    assert.equal(appointmentTest.calls.at(-1)[1].search_query, undefined);
  } finally { app.unmount(); host.remove(); }
});
