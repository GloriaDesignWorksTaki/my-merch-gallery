<?php
/**
 * 検索用：複数キーワード AND・LIKE エスケープ・大文字小文字無視
 * @package App\Support\Search
 */
namespace App\Support\Search;

use Illuminate\Database\Eloquent\Builder;

final class FlexibleSearch
{
  public static function tokens(string $raw): array
  {
    $trimmed = trim(preg_replace('/[\s\x{3000}]+/u', ' ', $raw) ?? '');
    if ($trimmed === '') {
      return [];
    }

    $parts = preg_split('/\s+/u', $trimmed, -1, PREG_SPLIT_NO_EMPTY);
    if ($parts === false) {
      return [];
    }

    return array_values(array_filter($parts, static fn (string $t): bool => $t !== ''));
  }

  public static function escapeLike(string $value): string
  {
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
  }

  public static function insensitiveLikePattern(string $token): string
  {
    return mb_strtolower('%'.self::escapeLike($token).'%', 'UTF-8');
  }

  public static function whereLowerLike(Builder $query, string $column, string $lowercasePattern): void
  {
    $wrapped = $query->getGrammar()->wrap($column);
    $query->whereRaw('LOWER('.$wrapped.') LIKE ?', [$lowercasePattern]);
  }

  public static function orWhereLowerLike(Builder $query, string $column, string $lowercasePattern): void
  {
    $wrapped = $query->getGrammar()->wrap($column);
    $query->orWhereRaw('LOWER('.$wrapped.') LIKE ?', [$lowercasePattern]);
  }

  public static function whereAllTokensMatch(Builder $query, array $tokens, callable $scopeOrFields): Builder
  {
    foreach ($tokens as $token) {
      $pattern = self::insensitiveLikePattern($token);
      $query->where(function (Builder $inner) use ($pattern, $scopeOrFields): void {
        $scopeOrFields($inner, $pattern);
      });
    }

    return $query;
  }
}
