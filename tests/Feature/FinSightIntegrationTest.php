<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Asset;
use App\Models\Portfolio;
use App\Models\PortfolioSnapshot;
use App\Models\TradingPair;
use App\Models\User;
use App\Models\UserTradingPair;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FinSightIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Carbon::setTestNow(
            Carbon::parse('2026-06-25 10:00:00', 'Asia/Kuala_Lumpur')
        );

        config([
            'services.openai.api_key' => 'test-openai-key',
            'services.openai.model' => 'test-model',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createUser(array $attributes = []): User
    {
        $isAdmin = (bool) ($attributes['is_admin'] ?? false);
        unset($attributes['is_admin']);

        $user = User::factory()->create(array_merge([
            'status' => 'active',
        ], $attributes));

        $user->forceFill([
            'is_admin' => $isAdmin,
        ])->save();

        return $user->fresh();
    }

    private function resetWebGuard(): void
    {
        $this->app['auth']->guard('web')->logout();
        $this->app['auth']->forgetGuards();
    }

    private function lunoPortfolioHttpFake(): void
    {
        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/api/1/balance')) {
                return Http::response([
                    'balance' => [
                        ['asset' => 'MYR', 'balance' => '1500.50'],
                        ['asset' => 'XBT', 'balance' => '0.025'],
                        ['asset' => 'ETH', 'balance' => '1.500'],
                    ],
                ], 200);
            }

            if (str_contains($request->url(), '/api/1/ticker')) {
                $query = [];
                parse_str(
                    (string) parse_url($request->url(), PHP_URL_QUERY),
                    $query
                );

                $pair = $query['pair']
                    ?? ($request->data()['pair'] ?? '');

                return match ($pair) {
                    'XBTMYR' => Http::response([
                        'last_trade' => '250000.00',
                        'bid' => '249900.00',
                        'ask' => '250100.00',
                    ], 200),
                    'ETHMYR' => Http::response([
                        'last_trade' => '12000.00',
                        'bid' => '11990.00',
                        'ask' => '12010.00',
                    ], 200),
                    default => Http::response([], 404),
                };
            }

            return Http::response([], 404);
        });
    }

    private function newsResponse(): array
    {
        return [
            'Data' => [
                [
                    'title' => 'Bitcoin adoption continues',
                    'body' => 'Institutional demand for bitcoin rises.',
                    'tags' => 'BTC|Bitcoin',
                    'categories' => 'Blockchain',
                    'source' => 'Source A',
                    'url' => 'https://example.test/bitcoin',
                    'published_on' => 1767225600,
                ],
                [
                    'title' => 'Ethereum network update',
                    'body' => 'Ethereum developers publish an update.',
                    'tags' => 'ETH',
                    'categories' => 'Technology',
                    'source' => 'Source B',
                    'url' => 'https://example.test/ethereum',
                    'published_on' => 1767225600,
                ],
            ],
        ];
    }

    private function sentimentResponse(): array
    {
        return [
            'average' => [
                'positive' => 0.20,
                'negative' => 0.60,
                'neutral' => 0.20,
            ],
            'risk_score' => 60,
            'risk_level' => 'High',
            'results' => [
                [
                    'text' => 'Bitcoin adoption continues',
                    'sentiment' => 'negative',
                    'positive' => 0.20,
                    'negative' => 0.60,
                    'neutral' => 0.20,
                ],
            ],
        ];
    }

    private function openAIResponse(string $text): array
    {
        return [
            'output' => [
                [
                    'type' => 'message',
                    'content' => [
                        [
                            'text' => $text,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function test_it_01_luno_credentials_and_portfolio_synchronisation(): void
    {
        $user = $this->createUser();
        $btc = Asset::create([
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
        ]);

        Portfolio::create([
            'user_id' => $user->id,
            'asset_id' => $btc->id,
            'amount' => 0.01,
        ]);

        $this->actingAs($user, 'web')
            ->post(route('settings.api-key.store'), [
                'api_key' => 'luno_test_key_001',
                'api_secret' => 'luno_test_secret_001',
            ])
            ->assertSessionHasNoErrors();

        $storedKey = ApiKey::where('user_id', $user->id)
            ->where('exchange', 'Luno')
            ->firstOrFail();

        $this->assertSame(
            'luno_test_key_001',
            Crypt::decryptString($storedKey->api_key)
        );

        $this->assertSame(
            'luno_test_secret_001',
            Crypt::decryptString($storedKey->api_secret)
        );

        $this->lunoPortfolioHttpFake();

        $this->actingAs($user, 'web')
            ->post(route('sync.luno'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas(
                'success',
                'Portfolio synced successfully.'
            );

        // Repeat the synchronisation to verify update without duplication.
        $this->actingAs($user, 'web')
            ->post(route('sync.luno'))
            ->assertRedirect(route('dashboard'));

        $btcPortfolio = Portfolio::where('user_id', $user->id)
            ->whereHas('asset', fn ($query) =>
                $query->where('symbol', 'BTC'))
            ->firstOrFail();

        $ethPortfolio = Portfolio::where('user_id', $user->id)
            ->whereHas('asset', fn ($query) =>
                $query->where('symbol', 'ETH'))
            ->firstOrFail();

        $this->assertEqualsWithDelta(
            0.025,
            (float) $btcPortfolio->amount,
            0.000001
        );

        $this->assertEqualsWithDelta(
            1.5,
            (float) $ethPortfolio->amount,
            0.000001
        );

        $this->assertSame(
            1,
            Portfolio::where('user_id', $user->id)
                ->where('asset_id', $btc->id)
                ->count()
        );
    }

    public function test_it_02_balance_ticker_wallet_and_snapshot_integration(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->post(route('settings.api-key.store'), [
                'api_key' => 'luno_test_key_001',
                'api_secret' => 'luno_test_secret_001',
            ])
            ->assertSessionHasNoErrors();

        $this->lunoPortfolioHttpFake();

        $this->actingAs($user, 'web')
            ->post(route('sync.luno'))
            ->assertRedirect(route('dashboard'));

        $wallet = Wallet::where('user_id', $user->id)
            ->firstOrFail();

        $snapshot = PortfolioSnapshot::where('user_id', $user->id)
            ->firstOrFail();

        $this->assertEqualsWithDelta(
            1500.50,
            (float) $wallet->cash,
            0.001
        );

        $this->assertSame(
            Carbon::today('Asia/Kuala_Lumpur')->toDateString(),
            $snapshot->date->toDateString()
        );

        $this->assertEqualsWithDelta(
            25750.50,
            (float) $snapshot->total_value,
            0.001
        );
    }

    public function test_it_03_admin_pair_import_status_and_user_selection_integration(): void
    {
        $admin = $this->createUser(['is_admin' => true]);

        Http::fake([
            'https://api.luno.com/api/exchange/1/markets' =>
                Http::response([
                    'markets' => [
                        ['market_id' => 'XBTMYR'],
                        ['market_id' => 'ETHMYR'],
                        ['market_id' => 'SOLMYR'],
                        ['market_id' => 'UNSUPPORTEDPAIR'],
                    ],
                ], 200),
        ]);

        $this->actingAs($admin, 'web')
            ->post(route('admin.pairs.import-luno'))
            ->assertRedirect();

        $xbt = TradingPair::where('symbol', 'XBTMYR')
            ->firstOrFail();
        $eth = TradingPair::where('symbol', 'ETHMYR')
            ->firstOrFail();
        $sol = TradingPair::where('symbol', 'SOLMYR')
            ->firstOrFail();

        $this->assertDatabaseMissing('trading_pairs', [
            'symbol' => 'UNSUPPORTEDPAIR',
        ]);

        $this->actingAs($admin, 'web')
            ->patch(route('admin.pairs.toggle', $eth))
            ->assertRedirect();

        $this->assertFalse(
            (bool) $eth->fresh()->is_active
        );

        $this->resetWebGuard();

        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->post(route('settings.pairs.store'), [
                'trading_pair_id' => $sol->id,
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($user, 'web')
            ->get(route('settings'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Settings')
                ->has('availablePairs', 1)
                ->where('availablePairs.0.id', $xbt->id)
                ->where('availablePairs.0.symbol', 'XBTMYR')
                ->has('selectedPairs', 1));

        $this->actingAs($user, 'web')
            ->post(route('settings.pairs.store'), [
                'trading_pair_id' => $sol->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(
            1,
            UserTradingPair::where('user_id', $user->id)
                ->where('trading_pair_id', $sol->id)
                ->count()
        );
    }

    public function test_it_04_news_filtering_and_market_analysis_delivery(): void
    {
        $user = $this->createUser();

        Http::fake([
            'https://min-api.cryptocompare.com/data/v2/news/*' =>
                Http::sequence()
                    ->push($this->newsResponse(), 200)
                    ->push(['Data' => []], 200),
            'http://127.0.0.1:5001/analyze-sentiment' =>
                Http::sequence()
                    ->push($this->sentimentResponse(), 200)
                    ->push(['error' => 'No texts provided'], 400),
            'https://api.openai.com/v1/responses' =>
                Http::sequence()
                    ->push(
                        $this->openAIResponse(
                            'Bitcoin risk remains elevated.'
                        ),
                        200
                    )
                    ->push(['error' => 'service unavailable'], 500),
        ]);

        $this->actingAs($user, 'web')
            ->postJson(route('market.analyze'), [
                'symbol' => 'BTC',
            ])
            ->assertOk()
            ->assertJsonPath('symbol', 'BTC')
            ->assertJsonPath(
                'news.0.title',
                'Bitcoin adoption continues'
            )
            ->assertJsonPath(
                'explanation',
                'Bitcoin risk remains elevated.'
            );

        $this->actingAs($user, 'web')
            ->postJson(route('market.analyze'), [
                'symbol' => 'BTC',
            ])
            ->assertOk()
            ->assertJsonCount(0, 'news')
            ->assertJsonPath(
                'explanation',
                'AI explanation is currently unavailable.'
            );
    }

    public function test_it_05_news_to_finbert_risk_output_integration(): void
    {
        $user = $this->createUser();

        Http::fake([
            'https://min-api.cryptocompare.com/data/v2/news/*' =>
                Http::sequence()
                    ->push($this->newsResponse(), 200)
                    ->push($this->newsResponse(), 200),
            'http://127.0.0.1:5001/analyze-sentiment' =>
                Http::sequence()
                    ->push($this->sentimentResponse(), 200)
                    ->push(['error' => 'service unavailable'], 500),
            'https://api.openai.com/v1/responses' =>
                Http::sequence()
                    ->push(
                        $this->openAIResponse(
                            'The market remains cautious.'
                        ),
                        200
                    )
                    ->push(['error' => 'service unavailable'], 500),
        ]);

        $this->actingAs($user, 'web')
            ->getJson(route('market.summary'))
            ->assertOk()
            ->assertJsonPath('sentiment.risk_score', 60)
            ->assertJsonPath('sentiment.risk_level', 'High')
            ->assertJsonPath(
                'sentiment.average.negative',
                0.6
            );

        // FinBERT failure is returned in a controlled JSON structure.
        $this->actingAs($user, 'web')
            ->getJson(route('market.summary'))
            ->assertOk()
            ->assertJsonPath(
                'sentiment.error',
                'service unavailable'
            )
            ->assertJsonPath(
                'summary',
                'Unable to generate market summary.'
            );
    }

    public function test_it_06_market_abstract_workflow_and_fallback(): void
    {
        $user = $this->createUser();

        Http::fake([
            'https://min-api.cryptocompare.com/data/v2/news/*' =>
                Http::sequence()
                    ->push($this->newsResponse(), 200)
                    ->push($this->newsResponse(), 200),
            'http://127.0.0.1:5001/analyze-sentiment' =>
                Http::sequence()
                    ->push($this->sentimentResponse(), 200)
                    ->push($this->sentimentResponse(), 200),
            'https://api.openai.com/v1/responses' =>
                Http::sequence()
                    ->push(
                        $this->openAIResponse(
                            'The market remains cautious amid negative sentiment.'
                        ),
                        200
                    )
                    ->push(['error' => 'service unavailable'], 500),
        ]);

        $this->actingAs($user, 'web')
            ->getJson(route('market.summary'))
            ->assertOk()
            ->assertJsonPath(
                'summary',
                'The market remains cautious amid negative sentiment.'
            )
            ->assertJsonPath('sentiment.risk_level', 'High');

        $this->actingAs($user, 'web')
            ->getJson(route('market.summary'))
            ->assertOk()
            ->assertJsonPath(
                'summary',
                'Unable to generate market summary.'
            );
    }

    public function test_it_07_asset_deep_dive_workflow_and_fallback(): void
    {
        $user = $this->createUser();

        Http::fake([
            'https://min-api.cryptocompare.com/data/v2/news/*' =>
                Http::sequence()
                    ->push($this->newsResponse(), 200)
                    ->push($this->newsResponse(), 200),
            'http://127.0.0.1:5001/analyze-sentiment' =>
                Http::sequence()
                    ->push($this->sentimentResponse(), 200)
                    ->push($this->sentimentResponse(), 200),
            'https://api.openai.com/v1/responses' =>
                Http::sequence()
                    ->push(
                        $this->openAIResponse(
                            'Bitcoin faces elevated news-driven risk.'
                        ),
                        200
                    )
                    ->push(['error' => 'service unavailable'], 500),
        ]);

        $this->actingAs($user, 'web')
            ->postJson(route('market.analyze'), [
                'symbol' => 'BTC',
            ])
            ->assertOk()
            ->assertJsonPath('symbol', 'BTC')
            ->assertJsonPath('sentiment.risk_score', 60)
            ->assertJsonPath(
                'explanation',
                'Bitcoin faces elevated news-driven risk.'
            );

        $this->actingAs($user, 'web')
            ->postJson(route('market.analyze'), [
                'symbol' => 'BTC',
            ])
            ->assertOk()
            ->assertJsonPath(
                'explanation',
                'AI explanation is currently unavailable.'
            );
    }

    public function test_it_08_portfolio_database_to_dashboard_integration(): void
    {
        $user = $this->createUser();

        $btc = Asset::create([
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
        ]);

        $eth = Asset::create([
            'symbol' => 'ETH',
            'name' => 'Ethereum',
        ]);

        Portfolio::create([
            'user_id' => $user->id,
            'asset_id' => $btc->id,
            'amount' => 0.025,
        ]);

        Portfolio::create([
            'user_id' => $user->id,
            'asset_id' => $eth->id,
            'amount' => 1.5,
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'cash' => 1500.50,
        ]);

        PortfolioSnapshot::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'total_value' => 10000,
        ]);

        PortfolioSnapshot::create([
            'user_id' => $user->id,
            'date' => '2026-06-25',
            'total_value' => 12000,
        ]);

        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/api/1/ticker')) {
                $query = [];
                parse_str(
                    (string) parse_url($request->url(), PHP_URL_QUERY),
                    $query
                );

                $pair = $query['pair']
                    ?? ($request->data()['pair'] ?? '');

                return match ($pair) {
                    'XBTMYR' => Http::response([
                        'last_trade' => '250000.00',
                    ], 200),
                    'ETHMYR' => Http::response([
                        'last_trade' => '12000.00',
                    ], 200),
                    default => Http::response([], 404),
                };
            }

            return Http::response([], 404);
        });

        $this->actingAs($user, 'web')
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('portfolios', 2)
                ->where(
                    'cash',
                    fn ($actual) =>
                        abs((float) $actual - 1500.50) < 0.001
                )
                ->where(
                    'totalBalance',
                    fn ($actual) =>
                        abs((float) $actual - 25750.50) < 0.001
                )
                ->has('snapshots', 2)
                ->where(
                    'changeAmount',
                    fn ($actual) =>
                        abs((float) $actual - 2000.00) < 0.001
                )
                ->where(
                    'percentChange',
                    fn ($actual) =>
                        abs((float) $actual - 20.00) < 0.001
                ));
    }

    public function test_it_09_snapshot_to_leaderboard_integration(): void
    {
        $userA = $this->createUser(['name' => 'User A']);
        $userB = $this->createUser(['name' => 'User B']);
        $userC = $this->createUser(['name' => 'User C']);
        $admin = $this->createUser([
            'name' => 'Admin User',
            'is_admin' => true,
        ]);

        PortfolioSnapshot::create([
            'user_id' => $userA->id,
            'date' => '2026-06-24',
            'total_value' => 20000,
        ]);

        PortfolioSnapshot::create([
            'user_id' => $userA->id,
            'date' => '2026-06-25',
            'total_value' => 25000,
        ]);

        PortfolioSnapshot::create([
            'user_id' => $userB->id,
            'date' => '2026-06-25',
            'total_value' => 10000,
        ]);

        PortfolioSnapshot::create([
            'user_id' => $admin->id,
            'date' => '2026-06-25',
            'total_value' => 99999,
        ]);

        $response = $this->actingAs($userA, 'web')
            ->getJson(route('leaderboard.data'))
            ->assertOk()
            ->assertJsonCount(3);

        $response->assertJsonPath('0.rank', 1);
        $response->assertJsonPath('0.name', 'User A (You)');
        $response->assertJsonPath('0.total_value', 25000);

        $response->assertJsonPath('1.rank', 2);
        $response->assertJsonPath('1.name', 'User B');
        $response->assertJsonPath('1.total_value', 10000);

        $response->assertJsonPath('2.rank', 3);
        $response->assertJsonPath('2.name', 'User C');
        $response->assertJsonPath('2.total_value', 0);

        $this->assertStringNotContainsString(
            'Admin User',
            $response->getContent()
        );

        $this->assertStringNotContainsString(
            $userA->email,
            $response->getContent()
        );
    }

    public function test_it_10_authentication_status_role_and_ownership_integration(): void
    {
        $this->get(route('settings'))
            ->assertRedirect(route('login'));

        $normalUser = $this->createUser();

        $this->actingAs($normalUser, 'web')
            ->get(route('admin.users'))
            ->assertForbidden();

        $this->resetWebGuard();

        $admin = $this->createUser([
            'is_admin' => true,
        ]);

        $this->actingAs($admin, 'web')
            ->get(route('admin.users'))
            ->assertOk();

        $this->resetWebGuard();

        $bannedUser = $this->createUser([
            'status' => 'banned',
        ]);

        $response = $this
            ->actingAs($bannedUser, 'web')
            ->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas(
            'error',
            'Your account has been banned.'
        );
        $this->assertGuest('web');

        $this->app['auth']->forgetGuards();

        $owner = $this->createUser();
        $otherUser = $this->createUser();

        $pair = TradingPair::create([
            'symbol' => 'XBTMYR',
            'source' => 'Luno',
            'is_active' => true,
        ]);

        $ownerSelection = UserTradingPair::create([
            'user_id' => $owner->id,
            'trading_pair_id' => $pair->id,
        ]);

        $this->actingAs($otherUser, 'web')
            ->delete(route(
                'settings.pairs.destroy',
                $ownerSelection
            ))
            ->assertForbidden();

        $this->assertDatabaseHas('user_trading_pairs', [
            'id' => $ownerSelection->id,
            'user_id' => $owner->id,
        ]);
    }
}
