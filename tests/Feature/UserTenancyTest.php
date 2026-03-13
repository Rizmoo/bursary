<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ward;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_users_must_have_a_ward(): void
    {
        $this->expectException(ValidationException::class);

        User::factory()->make([
            'ward_id' => null,
            'is_admin' => false,
        ])->save();
    }

    public function test_admin_users_can_access_any_ward(): void
    {
        $panel = $this->createMock(Panel::class);
        $admin = User::factory()->admin()->create();
        $wardA = Ward::factory()->create();
        $wardB = Ward::factory()->create();

        $this->assertTrue($admin->canAccessTenant($wardA));
        $this->assertTrue($admin->canAccessTenant($wardB));
        $this->assertCount(2, $admin->getTenants($panel));
    }

    public function test_non_admin_users_only_access_their_assigned_ward(): void
    {
        $panel = $this->createMock(Panel::class);
        $assignedWard = Ward::factory()->create();
        $otherWard = Ward::factory()->create();
        $user = User::factory()->for($assignedWard)->create();

        $this->assertTrue($user->canAccessTenant($assignedWard));
        $this->assertFalse($user->canAccessTenant($otherWard));
        $this->assertCount(1, $user->getTenants($panel));
        $this->assertTrue($user->getTenants($panel)[0]->is($assignedWard));
    }
}
