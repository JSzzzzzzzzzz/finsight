<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class OpenAIService
{
    public function generateAssetExplanation(string $symbol, array $news, array $sentiment): string
    {
        $headlines = collect($news)
            ->pluck('title')
            ->take(5)
            ->implode("\n- ");

        $riskScore = $sentiment['risk_score'] ?? 0;
        $riskLevel = $sentiment['risk_level'] ?? 'Unknown';
        $average = $sentiment['average'] ?? [];

        $prompt = "
You are FinSight AI. Generate a short educational cryptocurrency risk explanation.

Asset: {$symbol}
Risk Level: {$riskLevel}
Risk Score: {$riskScore}/100
Average Sentiment:
Positive: " . ($average['positive'] ?? 0) . "
Neutral: " . ($average['neutral'] ?? 0) . "
Negative: " . ($average['negative'] ?? 0) . "

Latest headlines:
- {$headlines}

Write 2-3 sentences only. Avoid financial advice. Explain the key market driver.
";

        $response = Http::withToken(config('services.openai.api_key'))
            ->post('https://api.openai.com/v1/responses', [
                'model' => config('services.openai.model'),
                'input' => $prompt,
            ]);
        Log::info('OpenAI debug', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if (!$response->successful()) {
            return 'AI explanation is currently unavailable.';
        }

        $text = collect($response->json('output'))
            ->firstWhere('type', 'message')['content'][0]['text'] ?? null;

        return $text ?? 'AI explanation is currently unavailable.';
    }

    public function generateMarketAbstract(
        array $headlines,
        array $sentiment
    ): string {
        $headlineText = implode("\n- ", $headlines);

        $average = $sentiment['average'] ?? [];

        $prompt = "
You are FinSight AI.

Latest market sentiment:

Positive: " . ($average['positive'] ?? 0) . "
Neutral: " . ($average['neutral'] ?? 0) . "
Negative: " . ($average['negative'] ?? 0) . "

Latest crypto headlines:

- {$headlineText}

Generate a concise market abstract.

Requirements:
- 2 to 3 sentences only
- Professional tone
- Explain overall market condition
- Mention dominant sentiment
- No financial advice
";

        $response = Http::withToken(
            config('services.openai.api_key')
        )->post(
            'https://api.openai.com/v1/responses',
            [
                'model' => config('services.openai.model'),
                'input' => $prompt,
            ]
        );

        Log::info('Market Abstract Debug', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if (!$response->successful()) {
            Log::error('Market Abstract OpenAI Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return 'Unable to generate market summary.';
        }

        $text = collect($response->json('output'))
            ->firstWhere('type', 'message')['content'][0]['text'] ?? null;

        return $text ?? 'Unable to generate market summary.';
    }
}
