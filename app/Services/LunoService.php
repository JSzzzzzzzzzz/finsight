<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Portfolio;
use App\Models\Wallet;
use App\Models\PortfolioSnapshot;
use App\Models\ApiKey;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class LunoService
{
    protected string $baseUrl = 'https://api.luno.com';

    protected string $apiKey;
    protected string $apiSecret;

    public function __construct()
    {
        $apiKeyRecord = ApiKey::where('user_id', Auth::id())
            ->where('exchange', 'Luno')
            ->first();

        if (!$apiKeyRecord) {
            $this->apiKey = '';
            $this->apiSecret = '';
            return;
        }

        $this->apiKey = Crypt::decryptString($apiKeyRecord->api_key);
        $this->apiSecret = Crypt::decryptString($apiKeyRecord->api_secret);
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

        if (!isset($data['balance'])) {
        throw new \Exception(
            $data['error'] ?? 'Unable to retrieve Luno balance. Please check your API key and secret.'
        );
    }

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
        $totalValue = 0;

        foreach ($data['crypto'] as $item) {

            $symbol = $item['symbol'];
            $amount = (float) $item['amount'];

            // create asset if not exists
            $asset = Asset::firstOrCreate(
                ['symbol' => $symbol],
                ['name' => $symbol]
            );

            // update portfolio ONLY amount
            Portfolio::updateOrCreate(
                [
                    'user_id' => $userId,
                    'asset_id' => $asset->id
                ],
                [
                    'amount' => $amount
                ]
            );
        }

        // calculate total value
        foreach ($data['crypto'] as $item) {
            $symbol = $item['symbol'];
            $amount = (float) $item['amount'];

            $pair = $symbol === 'BTC' ? 'XBTMYR' : $symbol . 'MYR';
            $ticker = $this->getTicker($pair);

            if (!isset($ticker['last_trade'])) continue;
            $price = (float) ($ticker['last_trade'] ?? 0);

            $totalValue += $amount * $price;
        }

        // add cash
        $totalValue += $data['cash_MYR'];

        // store snapshot
        PortfolioSnapshot::updateOrCreate(
            [
                'user_id' => $userId,
                'date' => Carbon::today('Asia/Kuala_Lumpur')->toDateString()
            ],
            [
                'total_value' => round($totalValue, 2)
            ]
        );

        // update wallet (fiat)
        Wallet::updateOrCreate(
            ['user_id' => $userId],
            ['cash' => $data['cash_MYR']]
        );

        return ['cash_MYR' => $data['cash_MYR']];
    }

    public function getMarkets()
    {
        $response = Http::get($this->baseUrl . '/api/exchange/1/markets');

        return $response->json();
    }

    public function getTicker($pair)
    {
        return Http::get($this->baseUrl . '/api/1/ticker', [
            'pair' => $pair
        ])->json();
    }

    public function getAllTickers()
    {
        $response = Http::get($this->baseUrl . '/api/1/tickers');

        return $response->json();
    }
}
