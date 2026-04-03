declare module '@inertiajs/core' {
  export interface PageProps {
    locale?: 'en' | 'ja';
    locales?: Array<{ code: 'en' | 'ja'; label: string }>;
  }
}
