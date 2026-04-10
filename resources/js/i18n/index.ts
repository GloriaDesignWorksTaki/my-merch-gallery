import { createI18n } from 'vue-i18n';
import en from '@/locales/en';
import ja from '@/locales/ja';

export type AppLocale = 'en' | 'ja';

export function createAppI18n(initialLocale: string | undefined) {
  const locale: AppLocale = initialLocale === 'en' ? 'en' : 'ja';
  return createI18n({
    legacy: false,
    locale,
    fallbackLocale: 'ja',
    messages: { en, ja },
  });
}

export function isAppLocale(value: unknown): value is AppLocale {
  return value === 'en' || value === 'ja';
}
