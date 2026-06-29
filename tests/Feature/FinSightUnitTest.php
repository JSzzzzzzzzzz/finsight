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
use App\Services\LunoService;
use App\Services\NewsService;
use App\Services\OpenAIService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class FinSightUnitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Inertia responses are tested without requiring a built Vite manifest.
        $this->withoutVite();

        Carbon::setTestNow(
            Carbon::parse('2026-06-27 10:00:00', 'Asia/Kuala_Lumpur')
        );
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

        $user->forceFill(['is_admin' => $isAdmin])->save();

        return $user->fresh();
    }

    private function storeDummyLunoCredentials(User $user): void
    {
        ApiKey::create([
            'user_id' => $user->id,
            'exchange' => 'Luno',
            'api_key' => Crypt::encryptString('luno_test_key_001'),
            'api_secret' => Crypt::encryptString('luno_test_secret_001'),
        ]);
    }

    private function addSnapshots(User $user, array $values): void
    {
        foreach ($values as $index => $value) {
            PortfolioSnapshot::create([
                'user_id' => $user->id,
                'date' => Carbon::parse('2026-06-20')
                    ->addDays($index)
                    ->toDateString(),
                'total_value' => $value,
            ]);
        }
    }

    private function assertDashboardChange(
        User $user,
        float $expectedAmount,
        float $expectedPercentage
    ): void {
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Dashboard')
                ->where(
                    'changeAmount',
                    fn($actual) =>
                    abs((float) $actual - $expectedAmount) < 0.001
                )
                ->where(
                    'percentChange',
                    fn($actual) =>
                    abs((float) $actual - $expectedPercentage) < 0.001
                ));
    }

    public function test_ut_01_authentication_registration_and_password_validation(): void
    {
        $validResponse = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $validResponse->assertSessionHasNoErrors();
        $this->assertAuthenticated();

        $createdUser = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('Password123!', $createdUser->password));

        $this->post('/logout');
        $this->assertGuest();

        $invalidResponse = $this->post('/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Different123!',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $invalidResponse->assertSessionHasErrors([
            'name',
            'email',
            'password',
        ]);

        $duplicateResponse = $this->post('/register', [
            'name' => 'Duplicate User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $duplicateResponse->assertSessionHasErrors('email');

        $loginResponse = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $loginResponse->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($createdUser);

        $this->post('/logout');

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_ut_02_api_key_input_validation_and_encryption(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->post(route('settings.api-key.store'), [
                'api_key' => '',
                'api_secret' => '',
            ])
            ->assertSessionHasErrors(['api_key', 'api_secret']);

        $this->actingAs($user)
            ->post(route('settings.api-key.store'), [
                'api_key' => 'luno_test_key_001',
                'api_secret' => 'luno_test_secret_001',
            ])
            ->assertSessionHasNoErrors();

        $record = ApiKey::where('user_id', $user->id)
            ->where('exchange', 'Luno')
            ->firstOrFail();

        $this->assertNotSame('luno_test_key_001', $record->api_key);
        $this->assertNotSame('luno_test_secret_001', $record->api_secret);

        $this->assertSame(
            'luno_test_key_001',
            Crypt::decryptString($record->api_key)
        );

        $this->assertSame(
            'luno_test_secret_001',
            Crypt::decryptString($record->api_secret)
        );
    }

    public function test_ut_03_luno_balance_normalisation(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $this->storeDummyLunoCredentials($user);

        Http::fake([
            'https://api.luno.com/api/1/balance' => Http::sequence()
                ->push([
                    'balance' => [
                        ['asset' => 'MYR', 'balance' => '1500.50'],
                        ['asset' => 'XBT', 'balance' => '0.025'],
                        ['asset' => 'ETH', 'balance' => '1.500'],
                    ],
                ], 200)
                ->push([
                    'error' => 'Invalid API credentials',
                ], 401),
        ]);

        $service = new LunoService();
        $result = $service->getProcessedBalances();

        $this->assertEqualsWithDelta(
            1500.50,
            (float) $result['cash_MYR'],
            0.001
        );

        $this->assertSame('BTC', $result['crypto'][0]['symbol']);
        $this->assertEqualsWithDelta(
            0.025,
            (float) $result['crypto'][0]['amount'],
            0.000001
        );

        $this->assertSame('ETH', $result['crypto'][1]['symbol']);
        $this->assertEqualsWithDelta(
            1.5,
            (float) $result['crypto'][1]['amount'],
            0.000001
        );

        $exceptionMessage = null;

        try {
            $service->getProcessedBalances();
        } catch (\Exception $exception) {
            $exceptionMessage = $exception->getMessage();
        }

        $this->assertSame(
            'Invalid API credentials',
            $exceptionMessage
        );
    }

    public function test_ut_04_portfolio_synchronisation_and_valuation_processing(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $this->storeDummyLunoCredentials($user);

        $btc = Asset::create([
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
        ]);

        Portfolio::create([
            'user_id' => $user->id,
            'asset_id' => $btc->id,
            'amount' => 0.01,
        ]);

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
                    ], 200),
                    'ETHMYR' => Http::response([
                        'last_trade' => '12000.00',
                    ], 200),
                    default => Http::response([], 404),
                };
            }

            return Http::response([], 404);
        });

        (new LunoService())->syncPortfolio();

        $btcPortfolio = Portfolio::where('user_id', $user->id)
            ->whereHas('asset', fn($query) =>
            $query->where('symbol', 'BTC'))
            ->firstOrFail();

        $ethPortfolio = Portfolio::where('user_id', $user->id)
            ->whereHas('asset', fn($query) =>
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

        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        $this->assertEqualsWithDelta(
            1500.50,
            (float) $wallet->cash,
            0.001
        );

        $snapshot = PortfolioSnapshot::where('user_id', $user->id)
            ->firstOrFail();

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

    public function test_ut_05_portfolio_performance_calculation(): void
    {
        $increaseUser = $this->createUser();
        $this->addSnapshots($increaseUser, [10000, 12000]);
        $this->assertDashboardChange($increaseUser, 2000, 20);

        $decreaseUser = $this->createUser();
        $this->addSnapshots($decreaseUser, [12000, 9000]);
        $this->assertDashboardChange($decreaseUser, -3000, -25);

        $unchangedUser = $this->createUser();
        $this->addSnapshots($unchangedUser, [10000, 10000]);
        $this->assertDashboardChange($unchangedUser, 0, 0);

        $zeroPreviousUser = $this->createUser();
        $this->addSnapshots($zeroPreviousUser, [0, 5000]);
        $this->assertDashboardChange($zeroPreviousUser, 5000, 0);

        $singleSnapshotUser = $this->createUser();
        $this->addSnapshots($singleSnapshotUser, [10000]);
        $this->assertDashboardChange($singleSnapshotUser, 0, 0);
    }

    public function test_ut_07_cryptocurrency_news_filtering_and_fallback(): void
    {
        $newsResponse = [
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
                [
                    'title' => 'General cryptocurrency market news',
                    'body' => 'Digital assets trade in a narrow range.',
                    'tags' => 'Crypto',
                    'categories' => 'Market',
                    'source' => 'Source C',
                    'url' => 'https://example.test/general',
                    'published_on' => 1767225600,
                ],
            ],
        ];

        Http::fake([
            'https://min-api.cryptocompare.com/data/v2/news/*' =>
            Http::sequence()
                ->push($newsResponse, 200)
                ->push($newsResponse, 200)
                ->push($newsResponse, 200)
                ->push($newsResponse, 200)
                ->push(['Data' => []], 200),
        ]);

        $service = new NewsService();

        $btcNews = $service->getCryptoNews('BTC');
        $this->assertCount(1, $btcNews);
        $this->assertSame(
            'Bitcoin adoption continues',
            $btcNews[0]['title']
        );

        $xbtNews = $service->getCryptoNews('XBT');
        $this->assertCount(1, $xbtNews);
        $this->assertSame(
            'Bitcoin adoption continues',
            $xbtNews[0]['title']
        );

        $ethNews = $service->getCryptoNews('ETH');
        $this->assertCount(1, $ethNews);
        $this->assertSame(
            'Ethereum network update',
            $ethNews[0]['title']
        );

        $fallbackNews = $service->getCryptoNews('DOGE');
        $this->assertCount(3, $fallbackNews);

        $this->assertSame([], $service->getCryptoNews('BTC'));
    }

    public function test_ut_11_openai_response_extraction_and_fallback_handling(): void
    {
        config([
            'services.openai.api_key' => 'test-openai-key',
            'services.openai.model' => 'test-model',
        ]);

        Http::fake([
            'https://api.openai.com/v1/responses' =>
            Http::sequence()
                ->push([
                    'output' => [
                        [
                            'type' => 'message',
                            'content' => [
                                [
                                    'text' =>
                                    'The market remains cautious amid mixed news.',
                                ],
                            ],
                        ],
                    ],
                ], 200)
                ->push([
                    'error' => 'service unavailable',
                ], 500)
                ->push([
                    'error' => 'service unavailable',
                ], 500)
                ->push([
                    'output' => [],
                ], 200),
        ]);

        $service = new OpenAIService();

        $summary = $service->generateMarketAbstract(
            ['Headline A', 'Headline B'],
            [
                'average' => [
                    'positive' => 0.2,
                    'negative' => 0.6,
                    'neutral' => 0.2,
                ],
            ]
        );

        $this->assertSame(
            'The market remains cautious amid mixed news.',
            $summary
        );

        $this->assertSame(
            'Unable to generate market summary.',
            $service->generateMarketAbstract(
                ['Headline A'],
                ['average' => []]
            )
        );

        $this->assertSame(
            'AI explanation is currently unavailable.',
            $service->generateAssetExplanation(
                'BTC',
                [['title' => 'Headline A']],
                [
                    'risk_score' => 70,
                    'risk_level' => 'High',
                    'average' => [],
                ]
            )
        );

        $this->assertSame(
            'Unable to generate market summary.',
            $service->generateMarketAbstract(
                ['Headline A'],
                ['average' => []]
            )
        );
    }

    public function test_ut_12_active_trading_pair_filtering_and_duplicate_prevention(): void
    {
        $user = $this->createUser();

        $xbt = TradingPair::create([
            'symbol' => 'XBTMYR',
            'source' => 'Luno',
            'is_active' => true,
        ]);

        TradingPair::create([
            'symbol' => 'ETHMYR',
            'source' => 'Luno',
            'is_active' => false,
        ]);

        $sol = TradingPair::create([
            'symbol' => 'SOLMYR',
            'source' => 'Luno',
            'is_active' => true,
        ]);

        UserTradingPair::create([
            'user_id' => $user->id,
            'trading_pair_id' => $sol->id,
        ]);

        $this->actingAs($user)
            ->get(route('settings'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Settings')
                ->has('availablePairs', 1)
                ->where('availablePairs.0.id', $xbt->id)
                ->where('availablePairs.0.symbol', 'XBTMYR'));

        $this->actingAs($user)
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

    public function test_ut_13_leaderboard_ranking_and_display_name_processing(): void
    {
        $userA = $this->createUser(['name' => 'User A']);
        $userB = $this->createUser(['name' => 'User B']);
        $userC = $this->createUser(['name' => 'User C']);

        PortfolioSnapshot::create([
            'user_id' => $userA->id,
            'date' => '2026-06-26',
            'total_value' => 25000,
        ]);

        PortfolioSnapshot::create([
            'user_id' => $userB->id,
            'date' => '2026-06-26',
            'total_value' => 10000,
        ]);

        $response = $this->actingAs($userA)
            ->get(route('leaderboard.data'))
            ->assertOk();

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
            $userA->email,
            $response->getContent()
        );

        $this->assertStringNotContainsString(
            $userB->email,
            $response->getContent()
        );

        $this->assertStringNotContainsString(
            $userC->email,
            $response->getContent()
        );
    }

    public function test_ut_14_role_account_status_and_record_ownership_authorisation(): void
    {
        /*
    |--------------------------------------------------------------------------
    | Unauthenticated access
    |--------------------------------------------------------------------------
    */
        $this->get(route('settings'))
            ->assertRedirect(route('login'));

        /*
    |--------------------------------------------------------------------------
    | Normal user cannot access administrator functions
    |--------------------------------------------------------------------------
    */
        $normalUser = $this->createUser();

        $this->actingAs($normalUser, 'web')
            ->get(route('admin.users'))
            ->assertForbidden();

        /*
    |--------------------------------------------------------------------------
    | Reset the normal-user authentication state
    |--------------------------------------------------------------------------
    */
        $this->app['auth']->guard('web')->logout();
        $this->app['auth']->forgetGuards();

        /*
    |--------------------------------------------------------------------------
    | Administrator can access administrator functions
    |--------------------------------------------------------------------------
    */
        $admin = $this->createUser([
            'is_admin' => true,
        ]);

        $this->assertTrue(
            (bool) $admin->fresh()->is_admin
        );

        $this->actingAs($admin->fresh(), 'web')
            ->get(route('admin.users'))
            ->assertOk();

        /*
    |--------------------------------------------------------------------------
    | Reset the administrator authentication state
    |--------------------------------------------------------------------------
    */
        $this->app['auth']->guard('web')->logout();
        $this->app['auth']->forgetGuards();

        /*
    |--------------------------------------------------------------------------
    | Banned user is logged out and redirected
    |--------------------------------------------------------------------------
    */
        $bannedUser = $this->createUser([
            'status' => 'banned',
        ]);

        $this->assertSame(
            'banned',
            $bannedUser->fresh()->status
        );

        $this->assertFalse(
            (bool) $bannedUser->fresh()->is_admin
        );

        $response = $this
            ->actingAs($bannedUser->fresh(), 'web')
            ->get(route('dashboard'));

        $response->assertRedirect(route('login'));

        $response->assertSessionHas(
            'error',
            'Your account has been banned.'
        );

        $this->assertGuest('web');

        /*
    |--------------------------------------------------------------------------
    | Reset authentication before ownership test
    |--------------------------------------------------------------------------
    */
        $this->app['auth']->forgetGuards();

        /*
    |--------------------------------------------------------------------------
    | User cannot delete another user's selected trading pair
    |--------------------------------------------------------------------------
    */
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
