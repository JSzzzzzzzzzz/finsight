<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user_cannot_access_administrator_dashboard(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($user);

        $this->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_normal_user_cannot_access_system_health_endpoint(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($user);

        $this->get(route('admin.system.health.data'))
            ->assertForbidden();
    }

    public function test_administrator_can_access_administrator_dashboard(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin);

        $this->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_administrator_can_access_system_health_endpoint(): void
    {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin);

        $this->get(route('admin.system.health.data'))
            ->assertOk()
            ->assertJsonStructure([
                'system_status',
                'active_services',
                'total_services',
                'services',
                'total_users',
                'new_users_today',
                'checked_at',
            ]);
    }
}