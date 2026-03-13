<?php

namespace Database\Factories;

use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FinancialYear>
 */
class FinancialYearFactory extends Factory
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
            'name' => 'FY ' . fake()->year() . '/' . fake()->year(),
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_current' => false,
            'opening_balance' => 0,
            'closing_balance' => 0,
            'unutilised_amount' => 0,
            'budget' => fake()->numberBetween(1000000, 20000000),
        ];
    }
}
