<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Features;
use Tests\TestCase;
use App\Models\PortfolioSnapshot;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_accounts_and_related_snapshots_can_be_deleted(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');
        }

        $this->actingAs($user = User::factory()->create());

        PortfolioSnapshot::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'total_value' => 5000.00,
        ]);

        $response = $this->delete('/user', [
            'password' => 'password',
        ]);

        /*
     * Confirm that the deletion request completed successfully.
     */
        $response->assertRedirect();

        /*
     * Confirm that the user and related portfolio snapshots were deleted.
     */
        $this->assertNull($user->fresh());

        $this->assertDatabaseMissing('portfolio_snapshots', [
            'user_id' => $user->id,
        ]);

        /*
     * Clear the authenticated user cached by actingAs().
     */
        Auth::forgetGuards();

        /*
     * Confirm that the deleted account is no longer authenticated.
     */
        $this->assertGuest();

        /*
     * Confirm that protected pages can no longer be accessed.
     */
        $this->get('/dashboard')
            ->assertRedirect(route('login'));
    }
    
    public function test_correct_password_must_be_provided_before_account_can_be_deleted(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');
        }

        $this->actingAs($user = User::factory()->create());

        $this->delete('/user', [
            'password' => 'wrong-password',
        ]);

        $this->assertNotNull($user->fresh());
    }
}
