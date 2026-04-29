<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Portfolio;
use App\Models\Wallet;
use App\Models\Transaction;
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

        foreach ($data['crypto'] as $item) {

            $symbol = $item['symbol'];
            $newAmount = (float) $item['amount'];

            // find asset
            $asset = Asset::firstOrCreate(
                ['symbol' => $symbol],
                ['name' => $symbol]
            );

            // get existing portfolio
            $portfolio = Portfolio::where('user_id', $userId)
                ->where('asset_id', $asset->id)
                ->first();

            $oldAmount = $portfolio ? (float) $portfolio->amount : 0;
            $oldAvg    = $portfolio ? (float) $portfolio->avg_buy_price : 0;

            $diff = $newAmount - $oldAmount;

            // 🔥 Detect BUY
            if ($diff > 0) {

                $pair = $symbol === 'BTC' ? 'XBTMYR' : $symbol . 'MYR';

                $ticker = $this->getTicker($pair);
                $price  = (float) ($ticker['last_trade'] ?? 0);

                if ($price > 0) {

                    //  WAC formula
                    $bid = (float) $ticker['bid'];
                    $ask = (float) $ticker['ask'];
                    $price = ($bid + $ask) / 2; 
                    
                    $newAvg = (
                        ($oldAvg * $oldAmount) + ($price * $diff)
                    ) / ($oldAmount + $diff);

                    // save transaction
                    Transaction::create([
                        'user_id' => $userId,
                        'asset_id' => $asset->id,
                        'type' => 'buy',
                        'amount' => $diff,
                        'price' => $price,
                        'total' => $diff * $price,
                        'transaction_time' => now()
                    ]);
                } else {
                    $newAvg = $oldAvg;
                }
            }
            // 🔥 Detect SELL
            elseif ($diff < 0) {

                $newAvg = $oldAvg; // WAC rule: avg unchanged

                Transaction::create([
                    'user_id' => $userId,
                    'asset_id' => $asset->id,
                    'type' => 'sell',
                    'amount' => abs($diff),
                    'price' => 0,
                    'total' => 0,
                    'transaction_time' => now()
                ]);
            } else {
                $newAvg = $oldAvg;
            }

            //  Update portfolio
            Portfolio::updateOrCreate(
                [
                    'user_id' => $userId,
                    'asset_id' => $asset->id
                ],
                [
                    'amount' => $newAmount,
                    'avg_buy_price' => $newAvg
                ]
            );
        }

        // update wallet
        Wallet::updateOrCreate(
            ['user_id' => $userId],
            ['cash' => $data['cash_MYR']]
        );

        return ['cash_MYR' => $data['cash_MYR']];
    }

    public function getTicker($pair)
    {
        return Http::get($this->baseUrl . '/api/1/ticker', [
            'pair' => $pair
        ])->json();
    }
}
