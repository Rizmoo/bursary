<?php

namespace Database\Seeders;

use App\Models\InstitutionCategory;
use App\Models\Ward;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstitutionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lvals = [
            'Day Secondary School',
            'Boarding Secondary School',
            'Tertiary Institution',
            'University',
        ];


        $wards = Ward::query()->get();
        foreach ($wards as $ward) {
            foreach ($lvals as $lval) {
                InstitutionCategory::query()->firstOrCreate([
                    'name' => $lval,
                    'ward_id' => $ward->id,
                ]);
            }
        }


    }
}
