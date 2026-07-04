import { createI18n } from 'vue-i18n';

// Load all locale JSON files using Vite's import.meta.glob with eager:true
// This ensures the files are included in the production build.
const modules = import.meta.glob('./*.json', { eager: true });
const messages = {};

function resolveMessagePath(messageObject, path) {
  if (!messageObject || typeof path !== 'string') {
    return null;
  }

  return path.split('.').reduce((currentValue, segment) => {
    if (currentValue == null || typeof currentValue !== 'object') {
      return null;
    }

    if (Array.isArray(currentValue) && /^\d+$/.test(segment)) {
      return currentValue[Number(segment)] ?? null;
    }

    return Object.prototype.hasOwnProperty.call(currentValue, segment)
      ? currentValue[segment]
      : null;
  }, messageObject);
}

for (const path in modules) {
  // path example: './en.json' -> locale = 'en'
  const m = modules[path];
  const match = path.match(/\.\/([^.\/]+)\.json$/);
  if (match) {
    const locale = match[1];
    // module may be the JSON object (eager) or { default: {...} }
    messages[locale] = m.default ? m.default : m;
  }
}

const savedLocale = (typeof localStorage !== 'undefined' && localStorage.getItem('locale')) || 'fr';

export const i18n = createI18n({
  legacy: false,
  globalInjection: true,
  locale: savedLocale,
  fallbackLocale: 'en',
  messageResolver: resolveMessagePath,
  messages,
});

// TEMP DEBUG: log loaded locales and counts (remove after debugging)
if (typeof window !== 'undefined' && window.console) {
  try {
    console.log('i18n messages loaded:', Object.keys(messages));
    console.log('i18n current locale:', savedLocale, 'message count:', messages[savedLocale] ? Object.keys(messages[savedLocale]).length : 0);
  } catch (e) {
    console.warn('i18n debug log failed', e);
  }
}
