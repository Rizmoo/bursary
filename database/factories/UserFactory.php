<?php

namespace Database\Factories;

use App\Models\County;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ward_id' => Ward::factory(),
            'county_id' => fn (array $attributes) => Ward::query()->find($attributes['ward_id'])?->county_id ?? County::factory()->create()->getKey(),
            'is_admin' => false,
            'is_county_admin' => false,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'ward_id' => null,
            'county_id' => null,
            'is_admin' => true,
            'is_county_admin' => false,
        ]);
    }

    public function countyAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'ward_id' => null,
            'county_id' => County::factory(),
            'is_admin' => false,
            'is_county_admin' => true,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
