<?php

namespace App\Filament\Resources\InstitutionCategories\Pages;

use App\Filament\Resources\InstitutionCategories\InstitutionCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInstitutionCategory extends EditRecord
{
    protected static string $resource = InstitutionCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
