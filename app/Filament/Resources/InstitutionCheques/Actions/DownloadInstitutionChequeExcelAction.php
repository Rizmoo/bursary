<?php

namespace App\Filament\Resources\InstitutionCheques\Actions;

use App\Models\InstitutionCheque;
use Filament\Actions\Action;

class DownloadInstitutionChequeExcelAction
{
    public static function make(): Action
    {
        return Action::make('downloadExcel')
            ->label('Download Excel')
            ->icon('heroicon-o-table-cells')
            ->color('gray')
            ->url(fn (InstitutionCheque $record): string => route('institution-cheques.excel', [
                'tenant' => filament()->getTenant(),
                'institution_cheque' => $record,
            ]))
            ->openUrlInNewTab();
    }
}
