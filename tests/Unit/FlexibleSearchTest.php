<?php

namespace Tests\Unit;

use App\Support\Search\FlexibleSearch;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FlexibleSearchTest extends TestCase
{
    public static function tokenCases(): array
    {
        return [
            'empty' => ['', []],
            'spaces only' => ['   ', []],
            'single' => ['foo', ['foo']],
            'multi' => ['foo bar baz', ['foo', 'bar', 'baz']],
            'fullwidth space' => ['foo'.mb_chr(0x3000, 'UTF-8').'bar', ['foo', 'bar']],
            'collapse spaces' => ['  a   b  ', ['a', 'b']],
        ];
    }

    #[DataProvider('tokenCases')]
    public function test_tokens(string $raw, array $expected): void
    {
        $this->assertSame($expected, FlexibleSearch::tokens($raw));
    }

    public function test_escape_like_escapes_metacharacters(): void
    {
        $this->assertSame('\\%\\_\\\\', FlexibleSearch::escapeLike('%_\\'));
    }

    public function test_insensitive_like_pattern_is_lowercased(): void
    {
        $this->assertSame('%jimmy%', FlexibleSearch::insensitiveLikePattern('Jimmy'));
    }
}
