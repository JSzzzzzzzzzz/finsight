<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NewsService
{
    public function getCryptoNews(string $symbol): array
    {
        $keyword = $this->mapSymbolToKeyword($symbol);

        $response = Http::get('https://min-api.cryptocompare.com/data/v2/news/', [
            'lang' => 'EN',
            'api_key' => env('CRYPTOCOMPARE_API_KEY'),
        ]);

        $data = $response->json();

        $data = $response->json();

        $allNews = collect($data['Data'] ?? []);

        $filteredNews = $allNews->filter(function ($item) use ($keyword) {
            $title = strtolower($item['title'] ?? '');
            $body = strtolower($item['body'] ?? '');
            $tags = strtolower($item['tags'] ?? '');
            $categories = strtolower($item['categories'] ?? '');
            $keyword = strtolower($keyword);

            return str_contains($title, $keyword)
                || str_contains($body, $keyword)
                || str_contains($tags, $keyword)
                || str_contains($categories, $keyword);
        });

        if ($filteredNews->isEmpty()) {
            $filteredNews = $allNews;
        }

        return $filteredNews
            ->take(5)
            ->map(fn($item) => [
                'title' => $item['title'] ?? '',
                'source' => $item['source'] ?? '',
                'url' => $item['url'] ?? '',
                'published_at' => isset($item['published_on'])
                    ? date('Y-m-d H:i:s', $item['published_on'])
                    : null,
            ])
            ->values()
            ->toArray();
    }

    private function mapSymbolToKeyword(string $symbol): string
    {
        return match (strtoupper($symbol)) {
            'BTC', 'XBT' => 'bitcoin',
            'ETH' => 'ethereum',
            'XRP' => 'xrp',
            'ADA' => 'cardano',
            'LINK' => 'chainlink',
            'LTC' => 'litecoin',
            default => strtolower($symbol),
        };
    }
}
