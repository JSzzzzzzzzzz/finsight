<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Portfolio;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class LunoService
{
    protected $baseUrl = 'https://api.luno.com';

    protected $apiKey;
    protected $apiSecret;

    public function __construct()
    {
        $this->apiKey = env('LUNO_API_KEY');
        $this->apiSecret = env('LUNO_API_SECRET');
    }

    public function getBalance()
    {
        $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
            ->get($this->baseUrl . '/api/1/balance');

        return $response->json();
    }

    public function getProcessedBalances()
    {
        $data = $this->getBalance();

        $crypto = [];
        $cash = 0;

        foreach ($data['balance'] as $item) {
            $asset = $item['asset'];
            $amount = (float) $item['balance'];

            if ($asset === 'MYR') {
                $cash = $amount;
                continue;
            }

            if ($asset === 'XBT') {
                $asset = 'BTC';
            }

            $crypto[] = [
                'symbol' => $asset,
                'amount' => $amount
            ];
        }

        return [
            'crypto' => $crypto,
            'cash_MYR' => $cash
        ];
    }

    public function syncPortfolio()
    {
        $data = $this->getProcessedBalances();

        $userId = Auth::id();

        // Save crypto assets
        foreach ($data['crypto'] as $item) {

            //  Find or create asset
            $asset = Asset::firstOrCreate(
                ['symbol' => $item['symbol']],
                ['name' => $item['symbol']]
            );

            //  Update portfolio
            Portfolio::updateOrCreate(
                [
                    'user_id' => $userId,
                    'asset_id' => $asset->id
                ],
                [
                    'amount' => $item['amount']
                ]
            );
        }

        Wallet::updateOrCreate(
            ['user_id' => $userId],
            ['cash' => $data['cash_MYR']]
        );

        // Return cash separately
        return [
            'cash_MYR' => $data['cash_MYR']
        ];
    }
}
