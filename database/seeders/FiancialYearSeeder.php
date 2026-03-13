<?php

namespace Database\Seeders;

use App\Models\FinancialYear;
use App\Models\Ward;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FiancialYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ward = Ward::query()->first();

        if (! $ward) {
            return;
        }

        FinancialYear::factory()->create([
            'ward_id' => $ward->id,
            'name' => '2023/2024',
            'start_date' => '2023-07-01',
            'end_date' => '2024-06-30',
            'is_current' => true,
            'budget' => 10000000,
            'opening_balance' => 0,
            'closing_balance' => 0,
            'unutilised_amount' => 0,
        ]);
    }
}
