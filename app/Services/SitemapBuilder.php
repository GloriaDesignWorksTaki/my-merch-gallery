<?php

namespace App\Services;

use App\Models\Band;
use App\Models\MerchItem;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Route;

/**
 * 公開ページの URL を DB から収集し sitemap XML を生成する（リクエストのたびに最新化）。
 */
class SitemapBuilder
{
    public function toXml(): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($this->entries() as $entry) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.$this->escapeXml($entry['loc']).'</loc>';
            if (! empty($entry['lastmod'])) {
                $lines[] = '    <lastmod>'.$this->escapeXml($entry['lastmod']).'</lastmod>';
            }
            if (! empty($entry['changefreq'])) {
                $lines[] = '    <changefreq>'.$this->escapeXml($entry['changefreq']).'</changefreq>';
            }
            if (! empty($entry['priority'])) {
                $lines[] = '    <priority>'.$this->escapeXml($entry['priority']).'</priority>';
            }
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }

    /**
     * @return list<array{loc: string, lastmod?: string, changefreq?: string, priority?: string}>
     */
    public function entries(): array
    {
        $out = [];

        $add = function (
            string $routeName,
            mixed $parameters = [],
            ?CarbonInterface $lastmod = null,
            string $changefreq = 'weekly',
            string $priority = '0.5',
        ) use (&$out): void {
            if (! Route::has($routeName)) {
                return;
            }
            $out[] = [
                'loc' => route($routeName, $parameters, true),
                'lastmod' => $lastmod?->timezone('UTC')->format('Y-m-d'),
                'changefreq' => $changefreq,
                'priority' => $priority,
            ];
        };

        $add('home', [], null, 'daily', '1.0');
        $add('bands.index', [], null, 'daily', '0.9');
        $add('merch-items.index', [], null, 'daily', '0.9');
        $add('search', [], null, 'weekly', '0.4');

        Band::query()
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->cursor()
            ->each(function (Band $band) use ($add): void {
                $add('bands.show', $band, $band->updated_at, 'weekly', '0.8');
            });

        MerchItem::query()
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->cursor()
            ->each(function (MerchItem $item) use ($add): void {
                $add('merch-items.show', $item, $item->updated_at, 'weekly', '0.8');
            });

        User::query()
            ->select(['id', 'updated_at'])
            ->whereNull('banned_at')
            ->orderBy('id')
            ->cursor()
            ->each(function (User $user) use ($add): void {
                $add('users.show', $user, $user->updated_at, 'weekly', '0.3');
            });

        return $out;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
