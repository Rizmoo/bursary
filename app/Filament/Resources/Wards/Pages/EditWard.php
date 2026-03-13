<?php

namespace App\Filament\Resources\Wards\Pages;

use App\Filament\Resources\Wards\WardResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWard extends EditRecord
{
    protected static string $resource = WardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
