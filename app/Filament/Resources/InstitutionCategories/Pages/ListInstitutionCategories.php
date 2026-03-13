<?php

namespace App\Filament\Resources\InstitutionCategories\Pages;

use App\Filament\Resources\InstitutionCategories\InstitutionCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstitutionCategories extends ListRecords
{
    protected static string $resource = InstitutionCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
