<?php

namespace App\Filament\Resources\InstitutionCategories\Pages;

use App\Filament\Resources\InstitutionCategories\InstitutionCategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInstitutionCategory extends ViewRecord
{
    protected static string $resource = InstitutionCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
