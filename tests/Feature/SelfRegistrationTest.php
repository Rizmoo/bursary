<?php

namespace Tests\Feature;

use App\Models\County;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelfRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_standalone_ward_registration_without_county(): void
    {
        $response = $this->post(route('self-register.store'), [
            'name'                  => 'Ward Manager',
            'email'                 => 'ward@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'county_name'           => '',   // intentionally blank
            'ward_name'             => 'Test Ward',
        ]);

        $ward = Ward::query()->where('name', 'Test Ward')->first();

        $this->assertNotNull($ward);
        $this->assertNull($ward->county_id, 'Standalone ward should have null county_id');

        $this->assertDatabaseHas('users', [
            'email'           => 'ward@example.com',
            'ward_id'         => $ward->getKey(),
            'county_id'       => null,
            'is_county_admin' => false,
            'is_admin'        => false,
        ]);

        $response->assertRedirect('/admin/' . $ward->getKey());
    }

    public function test_county_linked_ward_registration(): void
    {
        County::query()->create(['name' => 'Kirinyaga County']);

        $response = $this->post(route('self-register.store'), [
            'name'                  => 'Ward Manager',
            'email'                 => 'ward2@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'county_name'           => 'Kirinyaga County',
            'ward_name'             => 'Mwea Ward',
        ]);

        $ward = Ward::query()->where('name', 'Mwea Ward')->first();

        $this->assertNotNull($ward);
        $this->assertNotNull($ward->county_id, 'County-linked ward should have a county_id');
        $response->assertRedirect('/admin/' . $ward->getKey());
    }

    public function test_unknown_county_name_returns_validation_error(): void
    {
        $response = $this->from(route('self-register.create'))
            ->post(route('self-register.store'), [
                'name'                  => 'Ward Manager',
                'email'                 => 'ward3@example.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'county_name'           => 'Nonexistent County',
                'ward_name'             => 'Some Ward',
            ]);

        $response->assertRedirect(route('self-register.create'));
        $response->assertSessionHasErrors('county_name');
    }

    public function test_duplicate_ward_name_returns_validation_error(): void
    {
        Ward::factory()->create(['name' => 'Existing Ward', 'county_id' => null]);

        $response = $this->from(route('self-register.create'))
            ->post(route('self-register.store'), [
                'name'                  => 'Another Manager',
                'email'                 => 'ward4@example.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'county_name'           => '',
                'ward_name'             => 'Existing Ward',
            ]);

        $response->assertRedirect(route('self-register.create'));
        $response->assertSessionHasErrors('ward_name');
    }

    public function test_standalone_ward_user_can_access_admin_panel(): void
    {
        $ward = Ward::factory()->create(['county_id' => null]);
        $user = User::factory()->create([
            'ward_id'         => $ward->getKey(),
            'county_id'       => null,
            'is_admin'        => false,
            'is_county_admin' => false,
        ]);

        $this->actingAs($user);

        $this->assertTrue($user->canAccessPanel(
            $this->app->make(\Filament\Panel::class, ['id' => 'admin'])
        ));
    }
}
