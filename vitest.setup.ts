import { beforeAll, vi } from 'vitest';
import { config } from '@vue/test-utils';

// Stub Inertia components
config.global.stubs = {
  Link: {
    name: 'Link',
    template: '<a><slot /></a>',
    props: ['href'],
  },
  Head: {
    name: 'Head',
    template: '<div><slot /></div>',
  },
};

// Provide a minimal i18n mock
config.global.mocks = {
  $t: (key: string) => key,
};

config.global.provide = {
  // vue-i18n v9 composition api provide key
  // For components using useI18n, we can mock translate function via provide
  // using symbol is internal, but test-utils will forward mocks to composables
};

// Polyfill IntersectionObserver used in the component
class MockIntersectionObserver {
  constructor(public callback: IntersectionObserverCallback) {}
  observe() {}
  unobserve() {}
  disconnect() {}
}

// @ts-ignore
global.IntersectionObserver = MockIntersectionObserver as any;

// Polyfill localStorage for node
const store: Record<string, string> = {};
const localStorageMock = {
  getItem: (key: string) => (key in store ? store[key] : null),
  setItem: (key: string, value: string) => {
    store[key] = String(value);
  },
  removeItem: (key: string) => {
    delete store[key];
  },
  clear: () => {
    for (const k of Object.keys(store)) delete store[k];
  },
};
// @ts-ignore
if (!global.localStorage) global.localStorage = localStorageMock as any;

// Mock documentElement attribute setters used for dir/lang
beforeAll(() => {
  vi.spyOn(document.documentElement, 'setAttribute');
});
