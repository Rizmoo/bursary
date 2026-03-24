<?php

namespace App\Filament\Resources\Counties\Pages;

use App\Filament\Resources\Counties\CountyResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateCounty extends CreateRecord
{
    protected static string $resource = CountyResource::class;

    protected function afterCreate(): void
    {
        User::query()->create([
            'name' => (string) $this->data['county_admin_name'],
            'email' => (string) $this->data['county_admin_email'],
            'password' => (string) $this->data['county_admin_password'],
            'ward_id' => null,
            'county_id' => $this->record->getKey(),
            'is_admin' => false,
            'is_county_admin' => true,
        ]);
    }
}
