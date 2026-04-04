<?php

namespace App\Support\Search;

use Illuminate\Database\Eloquent\Builder;

/**
 * 空白区切りの複数キーワード（AND）と部分一致（LIKE）を組み立てる。
 * SQL の LIKE における % / _ はエスケープし、意図しないワイルドカード展開を防ぐ。
 */
final class FlexibleSearch
{
    /**
     * 全角スペースを含む連続空白を半角1つにし、キーワード配列に分割する。
     *
     * @return list<string>
     */
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

    /**
     * LIKE パターン内でメタ文字として扱われる文字をエスケープする。
     */
    public static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    /**
     * 大文字・小文字を区別しない比較用。エスケープ後に全体を Unicode 小文字化する。
     * LOWER(列) と比較することを想定。
     */
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

    /**
     * 各トークンについて「いずれかの列（またはコールバック内の OR 条件）に部分一致」を満たす行だけ残す（トークン間は AND）。
     * 比較は大文字・小文字を区別しない（LOWER(列) と {@see insensitiveLikePattern} の組み合わせを想定）。
     *
     * @param  callable(Builder $inner, string $lowercaseLikePattern): void  $scopeOrFields
     *                                                                                       $lowercaseLikePattern は % で囲まれたエスケープ・小文字化済み
     */
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
