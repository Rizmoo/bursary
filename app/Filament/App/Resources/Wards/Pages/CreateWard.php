<?php

namespace App\Filament\App\Resources\Wards\Pages;

use App\Filament\App\Resources\Wards\WardResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateWard extends CreateRecord
{
    protected static string $resource = WardResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['county_id'] = auth()->user()?->county_id;

        return $data;
    }

    protected function afterCreate(): void
    {
        User::query()->create([
            'name' => (string) $this->data['ward_user_name'],
            'email' => (string) $this->data['ward_user_email'],
            'password' => (string) $this->data['ward_user_password'],
            'ward_id' => $this->record->getKey(),
            'county_id' => $this->record->county_id,
            'is_admin' => false,
            'is_county_admin' => false,
        ]);
    }
}
