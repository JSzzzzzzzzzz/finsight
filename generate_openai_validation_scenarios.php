<?php

/**
 * Generate 12 controlled OpenAI validation scenarios by using the same
 * App\Services\OpenAIService methods and prompts used by FinSight.
 *
 * Place this file in the FinSight project root and run:
 *
 *   php generate_openai_validation_scenarios.php
 *
 * Default input locations checked:
 *   AI_Validation/finbert_validation_results.csv
 *   finbert_validation_results.csv
 *
 * Default output:
 *   AI_Validation/openai_validation_scenarios.csv
 */

declare(strict_types=1);

use App\Services\OpenAIService;
use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function findInputFile(): string
{
    $candidates = [
        __DIR__ . '/AI_Validation/finbert_validation_results.csv',
        __DIR__ . '/finbert_validation_results.csv',
    ];

    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            return $candidate;
        }
    }

    throw new RuntimeException(
        "finbert_validation_results.csv was not found. " .
        "Place it in AI_Validation or the FinSight project root."
    );
}

function readCsvById(string $path): array
{
    $handle = fopen($path, 'rb');

    if ($handle === false) {
        throw new RuntimeException("Unable to open input CSV: {$path}");
    }

    $headers = fgetcsv($handle);

    if ($headers === false) {
        fclose($handle);
        throw new RuntimeException('The input CSV does not contain a header row.');
    }

    $headers = array_map(
        static fn ($value) => trim((string) $value),
        $headers
    );

    // Remove a UTF-8 BOM that Excel may place before the first CSV header.
    if (isset($headers[0])) {
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
    }

    $required = [
        'id',
        'headline',
        'positive_probability',
        'negative_probability',
        'neutral_probability',
    ];

    foreach ($required as $column) {
        if (!in_array($column, $headers, true)) {
            fclose($handle);
            throw new RuntimeException(
                "Missing required CSV column: {$column}"
            );
        }
    }

    $rows = [];

    while (($values = fgetcsv($handle)) !== false) {
        if (count($values) !== count($headers)) {
            continue;
        }

        $row = array_combine($headers, $values);

        if ($row === false) {
            continue;
        }

        $id = (int) ($row['id'] ?? 0);

        if ($id > 0) {
            $rows[$id] = $row;
        }
    }

    fclose($handle);

    if ($rows === []) {
        throw new RuntimeException('No data rows were found in the input CSV.');
    }

    return $rows;
}

function averageSentiment(array $selectedRows): array
{
    $count = count($selectedRows);

    if ($count === 0) {
        throw new RuntimeException('A scenario must contain at least one headline.');
    }

    $positive = 0.0;
    $negative = 0.0;
    $neutral = 0.0;

    foreach ($selectedRows as $row) {
        $positive += (float) $row['positive_probability'];
        $negative += (float) $row['negative_probability'];
        $neutral += (float) $row['neutral_probability'];
    }

    $average = [
        'positive' => round($positive / $count, 4),
        'negative' => round($negative / $count, 4),
        'neutral' => round($neutral / $count, 4),
    ];

    $riskScore = (int) round($average['negative'] * 100);

    $riskLevel = match (true) {
        $riskScore >= 60 => 'High',
        $riskScore >= 35 => 'Medium',
        default => 'Low',
    };

    $dominantSentiment = array_search(max($average), $average, true);

    return [
        'average' => $average,
        'risk_score' => $riskScore,
        'risk_level' => $riskLevel,
        'dominant_sentiment' => $dominantSentiment,
    ];
}

function selectRows(array $rowsById, array $ids): array
{
    $selected = [];

    foreach ($ids as $id) {
        if (!isset($rowsById[$id])) {
            throw new RuntimeException(
                "Scenario references headline ID {$id}, but it was not found."
            );
        }

        $selected[] = $rowsById[$id];
    }

    return $selected;
}

function writeCsv(string $path, array $rows): void
{
    $directory = dirname($path);

    if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
        throw new RuntimeException("Unable to create output directory: {$directory}");
    }

    $handle = fopen($path, 'wb');

    if ($handle === false) {
        throw new RuntimeException("Unable to create output CSV: {$path}");
    }

    $headers = [
        'scenario_id',
        'output_type',
        'scenario_type',
        'asset',
        'headline_ids',
        'headlines',
        'average_positive',
        'average_negative',
        'average_neutral',
        'dominant_sentiment',
        'risk_score',
        'risk_level',
        'generated_output',
        'relevance',
        'sentiment_consistency',
        'factual_grounding',
        'clarity',
        'conciseness',
        'market_explanation_quality',
        'safety',
        'average_score',
        'researcher_comments',
    ];

    fputcsv($handle, $headers);

    foreach ($rows as $row) {
        fputcsv($handle, array_map(
            static fn ($header) => $row[$header] ?? '',
            $headers
        ));
    }

    fclose($handle);
}

try {
    $inputPath = findInputFile();
    $rowsById = readCsvById($inputPath);
    $openAI = app(OpenAIService::class);

    /*
     * 12 scenarios:
     * - 6 Market Abstract and 6 Deep Dive
     * - 3 positive, 3 negative, 3 neutral, and 3 mixed
     *
     * The IDs refer to the user's final 60-row validation dataset.
     */
    $scenarios = [
        [
            'scenario_id' => 'S01',
            'output_type' => 'Market Abstract',
            'scenario_type' => 'Positive',
            'asset' => 'MARKET',
            'ids' => [7, 13, 20, 48, 57],
        ],
        [
            'scenario_id' => 'S02',
            'output_type' => 'Deep Dive',
            'scenario_type' => 'Positive',
            'asset' => 'BTC',
            'ids' => [2, 7, 12, 55],
        ],
        [
            'scenario_id' => 'S03',
            'output_type' => 'Market Abstract',
            'scenario_type' => 'Positive',
            'asset' => 'MARKET',
            'ids' => [13, 20, 48, 52, 57],
        ],
        [
            'scenario_id' => 'S04',
            'output_type' => 'Market Abstract',
            'scenario_type' => 'Negative',
            'asset' => 'MARKET',
            'ids' => [5, 14, 23, 50, 58],
        ],
        [
            'scenario_id' => 'S05',
            'output_type' => 'Deep Dive',
            'scenario_type' => 'Negative',
            'asset' => 'BTC',
            'ids' => [14, 23, 36, 44],
        ],
        [
            'scenario_id' => 'S06',
            'output_type' => 'Deep Dive',
            'scenario_type' => 'Negative',
            'asset' => 'ETH',
            'ids' => [36, 46, 56, 58],
        ],
        [
            'scenario_id' => 'S07',
            'output_type' => 'Market Abstract',
            'scenario_type' => 'Neutral',
            'asset' => 'MARKET',
            'ids' => [6, 15, 32, 33, 45],
        ],
        [
            'scenario_id' => 'S08',
            'output_type' => 'Deep Dive',
            'scenario_type' => 'Neutral',
            'asset' => 'BTC',
            'ids' => [11, 24, 39, 54],
        ],
        [
            'scenario_id' => 'S09',
            'output_type' => 'Market Abstract',
            'scenario_type' => 'Neutral',
            'asset' => 'MARKET',
            'ids' => [16, 33, 45, 51],
        ],
        [
            'scenario_id' => 'S10',
            'output_type' => 'Market Abstract',
            'scenario_type' => 'Mixed',
            'asset' => 'MARKET',
            'ids' => [2, 5, 33, 48, 58],
        ],
        [
            'scenario_id' => 'S11',
            'output_type' => 'Deep Dive',
            'scenario_type' => 'Mixed',
            'asset' => 'BTC',
            'ids' => [7, 11, 14, 55],
        ],
        [
            'scenario_id' => 'S12',
            'output_type' => 'Deep Dive',
            'scenario_type' => 'Mixed',
            'asset' => 'XRP',
            'ids' => [5, 6, 10, 15, 47],
        ],
    ];

    $outputRows = [];

    foreach ($scenarios as $scenario) {
        $selectedRows = selectRows($rowsById, $scenario['ids']);
        $headlines = array_map(
            static fn ($row) => trim((string) $row['headline']),
            $selectedRows
        );

        $sentiment = averageSentiment($selectedRows);

        if ($scenario['output_type'] === 'Market Abstract') {
            $generatedOutput = $openAI->generateMarketAbstract(
                $headlines,
                [
                    'average' => $sentiment['average'],
                    'risk_score' => $sentiment['risk_score'],
                    'risk_level' => $sentiment['risk_level'],
                ]
            );
        } else {
            $news = array_map(
                static fn ($headline) => ['title' => $headline],
                $headlines
            );

            $generatedOutput = $openAI->generateAssetExplanation(
                $scenario['asset'],
                $news,
                [
                    'average' => $sentiment['average'],
                    'risk_score' => $sentiment['risk_score'],
                    'risk_level' => $sentiment['risk_level'],
                ]
            );
        }

        $outputRows[] = [
            'scenario_id' => $scenario['scenario_id'],
            'output_type' => $scenario['output_type'],
            'scenario_type' => $scenario['scenario_type'],
            'asset' => $scenario['asset'],
            'headline_ids' => implode(', ', $scenario['ids']),
            'headlines' => implode(' | ', $headlines),
            'average_positive' => $sentiment['average']['positive'],
            'average_negative' => $sentiment['average']['negative'],
            'average_neutral' => $sentiment['average']['neutral'],
            'dominant_sentiment' => $sentiment['dominant_sentiment'],
            'risk_score' => $sentiment['risk_score'],
            'risk_level' => $sentiment['risk_level'],
            'generated_output' => $generatedOutput,
            'relevance' => '',
            'sentiment_consistency' => '',
            'factual_grounding' => '',
            'clarity' => '',
            'conciseness' => '',
            'market_explanation_quality' => '',
            'safety' => '',
            'average_score' => '',
            'researcher_comments' => '',
        ];

        echo "{$scenario['scenario_id']} {$scenario['output_type']} generated.\n";
    }

    $outputPath = __DIR__ . '/AI_Validation/openai_validation_scenarios.csv';
    writeCsv($outputPath, $outputRows);

    echo "\nCompleted.\n";
    echo "Input: {$inputPath}\n";
    echo "Output: {$outputPath}\n";
    echo "Next: open the CSV and score every criterion from 1 to 5.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "ERROR: {$exception->getMessage()}\n");
    exit(1);
}
