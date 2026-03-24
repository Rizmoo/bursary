<?php

namespace App\Filament\Resources\InstitutionCheques\Actions;

use App\Models\InstitutionCheque;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Tables\Table;

class DownloadAllInstitutionChequesPdfAction
{
    public static function make(): Action
    {
        return Action::make('downloadAllPdf')
            ->label('Download All PDF')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->action(function () {
                // Increase memory and time limit for large PDF generation
                ini_set('memory_limit', '512M');
                set_time_limit(300);

                // Get all cheques for the current tenant/financial year
                $query = \App\Filament\Resources\InstitutionCheques\InstitutionChequeResource::getEloquentQuery();
                $records = $query->with([
                    'institution',
                    'financialYear',
                    'applicants' => fn ($query) => $query->orderBy('last_name')->orderBy('first_name'),
                ])->get();

                $tenant = filament()->getTenant();

                $pdf = Pdf::loadView('pdfs.institution-cheques-bulk', [
                    'cheques' => $records,
                    'tenant' => $tenant,
                ])->setPaper('a4', 'portrait');

                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    'institution-cheques-bulk-' . now()->format('YmdHis') . '.pdf'
                );
            });
    }
}
