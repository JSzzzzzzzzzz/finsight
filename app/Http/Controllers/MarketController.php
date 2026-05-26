<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Services\CryptoNewsService;
use App\Services\OpenAIService;

class MarketController extends Controller
{
    public function marketSummary(
        CryptoNewsService $newsService,
        OpenAIService $openAI
    ) {
        try {

            // Fetch latest BTC news
            $news = $newsService->getCryptoNews('BTC');

            // Take latest headlines
            $headlines = collect($news)
                ->pluck('title')
                ->take(10)
                ->values()
                ->all();

            // Send to FinBERT
            $sentimentResponse = Http::post(
                'http://127.0.0.1:5001/analyze-sentiment',
                [
                    'texts' => $headlines
                ]
            );

            $sentiment = $sentimentResponse->json();

            // Generate AI abstract
            $summary = $openAI->generateMarketAbstract(
                $headlines,
                $sentiment
            );

            return response()->json([
                'summary' => $summary,
                'sentiment' => $sentiment
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
