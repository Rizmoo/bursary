<?php

namespace Tests\Feature;

use App\Models\County;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_county_registration_creates_county_admin_and_redirects_to_app_panel(): void
    {
        $response = $this->post(route('county-register.store'), [
            'county_name' => 'Demo County',
            'name' => 'County Admin',
            'email' => 'county-admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/app');

        $county = County::query()->where('name', 'Demo County')->first();

        $this->assertNotNull($county);
        $this->assertDatabaseHas('users', [
            'email' => 'county-admin@example.com',
            'county_id' => $county?->getKey(),
            'is_county_admin' => true,
        ]);
    }

    public function test_existing_county_name_is_rejected_in_county_registration(): void
    {
        County::query()->create(['name' => 'Demo County']);

        $response = $this->from(route('county-register.create'))
            ->post(route('county-register.store'), [
                'county_name' => 'Demo County',
                'name' => 'County Admin',
                'email' => 'county-admin@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('county-register.create'));
        $response->assertSessionHasErrors('county_name');
    }

    public function test_county_admin_user_cannot_access_admin_panel(): void
    {
        $countyAdmin = User::factory()->countyAdmin()->create();
        $this->actingAs($countyAdmin);

        $response = $this->get('/admin');
        $response->assertForbidden();
    }
}
