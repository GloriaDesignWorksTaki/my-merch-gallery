export function notificationTargetUrl(data: Record<string, unknown>): string {
  const slug = data.merch_item_slug;
  if (typeof slug !== 'string' || slug === '') {
    return '/';
  }
  const base = route('merch-items.show', slug) as string;
  const commentId =
    (typeof data.reply_comment_id === 'number' ? data.reply_comment_id : null) ??
    (typeof data.comment_id === 'number' ? data.comment_id : null) ??
    (typeof data.parent_comment_id === 'number' ? data.parent_comment_id : null);
  if (commentId !== null && commentId !== undefined) {
    return `${base}#comment-${commentId}`;
  }
  return `${base}#comments`;
}
