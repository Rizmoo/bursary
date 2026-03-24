<?php

namespace Database\Factories;

use App\Models\County;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<County>
 */
class CountyFactory extends Factory
{
    protected $model = County::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->state() . ' County',
        ];
    }
}
