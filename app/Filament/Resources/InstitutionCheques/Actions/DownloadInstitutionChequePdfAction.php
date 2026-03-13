<?php

namespace App\Filament\Resources\InstitutionCheques\Actions;

use App\Models\InstitutionCheque;
use Filament\Actions\Action;

class DownloadInstitutionChequePdfAction
{
    public static function make(): Action
    {
        return Action::make('downloadPdf')
            ->label('Download PDF')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->url(fn (InstitutionCheque $record): string => route('institution-cheques.pdf', [
                'tenant' => filament()->getTenant(),
                'institution_cheque' => $record,
            ]))
            ->openUrlInNewTab();
    }
}
