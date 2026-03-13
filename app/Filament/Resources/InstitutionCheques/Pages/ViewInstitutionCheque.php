<?php

namespace App\Filament\Resources\InstitutionCheques\Pages;

use App\Filament\Resources\InstitutionCheques\Actions\DownloadInstitutionChequePdfAction;
use App\Filament\Resources\InstitutionCheques\Actions\DownloadInstitutionChequeExcelAction;
use App\Filament\Resources\InstitutionCheques\Actions\InstitutionChequeLifecycleActions;
use App\Filament\Resources\InstitutionCheques\InstitutionChequeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInstitutionCheque extends ViewRecord
{
    protected static string $resource = InstitutionChequeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DownloadInstitutionChequeExcelAction::make(),
            DownloadInstitutionChequePdfAction::make(),
            InstitutionChequeLifecycleActions::markCleared(),
            InstitutionChequeLifecycleActions::markStale(),
            InstitutionChequeLifecycleActions::returnToUnutilised(),
            EditAction::make(),
        ];
    }
}
