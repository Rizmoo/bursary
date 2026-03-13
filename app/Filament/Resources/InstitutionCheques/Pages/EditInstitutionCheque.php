<?php

namespace App\Filament\Resources\InstitutionCheques\Pages;

use App\Filament\Resources\InstitutionCheques\Actions\DownloadInstitutionChequePdfAction;
use App\Filament\Resources\InstitutionCheques\Actions\DownloadInstitutionChequeExcelAction;
use App\Filament\Resources\InstitutionCheques\Actions\InstitutionChequeLifecycleActions;
use App\Filament\Resources\InstitutionCheques\InstitutionChequeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInstitutionCheque extends EditRecord
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
            DeleteAction::make(),
        ];
    }
}
