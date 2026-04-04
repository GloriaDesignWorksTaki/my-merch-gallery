export function isSafeHttpUrl(href: string): boolean {
  try {
    const u = new URL(href, window.location.href);

    return u.protocol === 'http:' || u.protocol === 'https:';
  } catch {
    return false;
  }
}

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
