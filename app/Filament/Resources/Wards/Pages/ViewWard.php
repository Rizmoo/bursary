<?php

namespace App\Filament\Resources\Wards\Pages;

use App\Filament\Resources\Wards\WardResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWard extends ViewRecord
{
    protected static string $resource = WardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
