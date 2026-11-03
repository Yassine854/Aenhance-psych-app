import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';

const dom = new JSDOM('<!doctype html><div id="target"></div>');
globalThis.window = dom.window;
const { default: safeHtml } = await import('../resources/js/directives/safeHtml.js');

test('rich text preserves formatting and removes executable markup', () => {
  const el = dom.window.document.getElementById('target');
  safeHtml.beforeMount(el, { value: '<p><strong>Hello</strong></p><script>alert(1)</script><img src="x" onerror="alert(1)"><a href="javascript:alert(1)">click</a><svg onload="alert(1)"></svg><iframe src="https://evil.example"></iframe><form><input name="password"></form>' });
  assert.equal(el.querySelector('strong').textContent, 'Hello');
  assert.equal(el.querySelector('script,svg,iframe,form,input'), null);
  assert.equal(el.querySelector('img').hasAttribute('onerror'), false);
  assert.equal(el.querySelector('a').hasAttribute('href'), false);
  safeHtml.updated(el, { value: '<p onmouseover="alert(1)">Updated</p>', oldValue: '' });
  assert.equal(el.innerHTML, '<p>Updated</p>');
});

test('pagination labels remain functional', () => {
  const el = dom.window.document.createElement('span');
  safeHtml.beforeMount(el, { value: '&laquo; Previous' });
  assert.equal(el.textContent, '« Previous');
  safeHtml.updated(el, { value: null, oldValue: 'previous' });
  assert.equal(el.innerHTML, '');
});
