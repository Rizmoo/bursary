<?php

namespace Database\Factories;

use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InstitutionCategory>
 */
class InstitutionCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ward_id' => Ward::factory(),
            'name' => fake()->randomElement([
                'University',
                'Tertiary College',
                'Secondary School',
            ]),
        ];
    }
}
