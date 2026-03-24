<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ward;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {


       //call wards seeder
    
        $this->call(WardsTableSeeder::class);
        $this->call(FiancialYearSeeder::class);
        $this->call(InstitutionCategorySeeder::class);
    
        $demoWard = Ward::query()->firstOrCreate([
            'name' => 'Wamumu',
            'county_id' => Ward::query()->value('county_id'),
        ]);

        User::factory()->admin()->create([
            'name' => 'System Admin',
            'email' => 'admin@mail.com',
        ]);

        User::factory()->for($demoWard)->create([
            'name' => 'Wamumu Officer',
            'email' => 'ward@mail.com',
            'county_id' => $demoWard->county_id,
        ]);
    }
}
