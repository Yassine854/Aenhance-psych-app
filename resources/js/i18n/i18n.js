import { createI18n } from 'vue-i18n';
import en from './en.json';
import fr from './fr.json';
import ar from './ar.json';

const savedLocale = localStorage.getItem('locale') || 'en';

export const i18n = createI18n({
  locale: savedLocale,
  fallbackLocale: 'en',
  messages: { en, fr, ar },
});
