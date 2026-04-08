import type { AppTheme } from '@/types';

const STORAGE_KEY = 'mg-theme';

export function isAppTheme(v: unknown): v is AppTheme {
  return v === 'light' || v === 'dark' || v === 'primary';
}

export function syncAppTheme(pageProps: Record<string, unknown>): void {
  if (typeof document === 'undefined') {
    return;
  }

  const auth = pageProps.auth as { user?: { theme?: string | null } | null } | undefined;
  const userTheme = auth?.user?.theme;

  let theme: AppTheme = 'light';
  if (isAppTheme(userTheme)) {
    theme = userTheme;
  } else {
    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      if (isAppTheme(stored)) {
        theme = stored;
      }
    } catch {
      /* ignore */
    }
  }

  document.documentElement.dataset.theme = theme;

  if (theme === 'dark') {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }

  try {
    localStorage.setItem(STORAGE_KEY, theme);
  } catch {
    /* ignore */
  }
}
