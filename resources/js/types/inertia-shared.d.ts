declare module '@inertiajs/core' {
  export interface PageProps {
    appName?: string;
    locale?: 'en' | 'ja';
    locales?: Array<{ code: 'en' | 'ja'; label: string }>;
  }
}
