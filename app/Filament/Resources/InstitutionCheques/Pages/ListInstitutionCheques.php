<?php

namespace App\Filament\Resources\InstitutionCheques\Pages;

use App\Filament\Resources\InstitutionCheques\InstitutionChequeResource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\InstitutionCheques\Actions\DownloadAllInstitutionChequesPdfAction;

class ListInstitutionCheques extends ListRecords
{
    protected static string $resource = InstitutionChequeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DownloadAllInstitutionChequesPdfAction::make(),
        ];
    }
}
