<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Portfolio;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class HataService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $apiSecret;

    public function __construct()
    {
        $this->apiKey = env('HATA_API_KEY');
        $this->apiSecret = env('HATA_API_SECRET');
        $this->baseUrl = env('HATA_BASE_URL');
    }

    // public function getBalance()
    // {
    //     $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
    //         ->get($this->baseUrl . '/api/1/wallets'); 

    //     if (!$response->ok()) {
    //         dd($response->body());
    //     }

    //     return $response->json();
    // }

    // public function syncPortfolio()
    // {
    //     $data = $this->getBalance();
    //     $userId = auth()->id();

    //     foreach ($data['balances'] as $item) {

    //         if ($item['asset'] === 'MYR') continue;

    //         $symbol = $item['asset'];
    //         $amount = (float) $item['available'];

    //         $asset = \App\Models\Asset::firstOrCreate([
    //             'symbol' => $symbol
    //         ], [
    //             'name' => $symbol
    //         ]);

    //         \App\Models\Portfolio::updateOrCreate(
    //             [
    //                 'user_id' => $userId,
    //                 'asset_id' => $asset->id
    //             ],
    //             [
    //                 'amount' => $amount
    //             ]
    //         );
    //     }
    // }

    public function getTrades($pair)
    {
        $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
            ->get($this->baseUrl . '/api/1/trades', [
                'pair' => $pair
            ]);

        if (!$response->ok()) {
            dd($response->body());
        }

        return $response->json();
    }

    public function syncTransactions()
    {
        $userId = auth()->id();

        $pairs = ['BTCUSDT']; // extend later

        foreach ($pairs as $pair) {

            $trades = $this->getTrades($pair);

            foreach ($trades['data'] as $trade) {

                $symbol = 'BTC'; // map properly

                $asset = \App\Models\Asset::where('symbol', $symbol)->first();
                if (!$asset) continue;

                $amount = (float) $trade['qty'];
                $price  = (float) $trade['price'];
                $total  = $amount * $price;

                \App\Models\Transaction::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'trade_id' => $trade['trade_id']
                    ],
                    [
                        'asset_id' => $asset->id,
                        'type' => $trade['is_buy'] ? 'buy' : 'sell',
                        'amount' => $amount,
                        'price' => $price,
                        'total' => $total,
                        'transaction_time' => now()
                    ]
                );
            }
        }
    }
}
