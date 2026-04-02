/**
 * href がブラウザで開いてよい http / https か（javascript: 等を除外）。
 */
export function isSafeHttpUrl(href: string): boolean {
  try {
    const u = new URL(href, window.location.href);

    return u.protocol === 'http:' || u.protocol === 'https:';
  } catch {
    return false;
  }
}

/** 同一オリジン以外の http(s) リンクか */
export function isExternalHttpUrl(href: string): boolean {
  if (!isSafeHttpUrl(href)) {
    return false;
  }

  try {
    const u = new URL(href, window.location.href);

    return u.origin !== window.location.origin;
  } catch {
    return false;
  }
}
