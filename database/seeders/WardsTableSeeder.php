<?php

namespace Database\Seeders;

use App\Models\County;
use App\Models\Ward;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WardsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultCounty = County::query()->firstOrCreate([
            'name' => 'Kirinyaga County',
        ]);

        $wards = [
    // Mwea Constituency
    ['name' => 'Wamumu', 'constituency' => 'Mwea'],
    ['name' => 'Mutithi', 'constituency' => 'Mwea'],
    ['name' => 'Kangai', 'constituency' => 'Mwea'],
    ['name' => 'Thiba', 'constituency' => 'Mwea'],
    ['name' => 'Nyangati', 'constituency' => 'Mwea'],
    ['name' => 'Tebere', 'constituency' => 'Mwea'],
    ['name' => 'Gathigiriri', 'constituency' => 'Mwea'],
    ['name' => 'Murinduko', 'constituency' => 'Mwea'],

    // Gichugu Constituency
    ['name' => 'Kabare', 'constituency' => 'Gichugu'],
    ['name' => 'Baragwi', 'constituency' => 'Gichugu'],
    ['name' => 'Njukiini', 'constituency' => 'Gichugu'],
    ['name' => 'Ngariama', 'constituency' => 'Gichugu'],
    ['name' => 'Karumandi', 'constituency' => 'Gichugu'],

    // Ndia Constituency
    ['name' => 'Mukure', 'constituency' => 'Ndia'],
    ['name' => 'Kiine', 'constituency' => 'Ndia'],
    ['name' => 'Kariti', 'constituency' => 'Ndia'],

    // Kirinyaga Central Constituency
    ['name' => 'Mutira', 'constituency' => 'Kirinyaga Central'],
    ['name' => 'Kanyekini', 'constituency' => 'Kirinyaga Central'],
    ['name' => 'Kerugoya', 'constituency' => 'Kirinyaga Central'],
    ['name' => 'Inoi', 'constituency' => 'Kirinyaga Central'],
];

        foreach ($wards as $ward) {
            Ward::query()->firstOrCreate([
                'name' => $ward['name'],
                'county_id' => $defaultCounty->getKey(),
            ]);
        }
    }
}
