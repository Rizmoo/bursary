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
            ->requiresConfirmation()
            ->modalHeading('Generate Bulk PDF')
            ->modalDescription('This may take a few minutes. Once ready, your PDF will be available in the "Bulk PDF Exports" list for download.')
            ->modalSubmitActionLabel('Generate PDF')
            ->action(function () {
                $tenant = filament()->getTenant();

                // Prevent duplicate in-flight exports
                // $alreadyRunning = \App\Models\PdfExport::query()
                //     ->where('user_id', auth()->id())
                //     ->where('institution_id', $tenant->id)
                //     ->whereIn('status', ['pending', 'processing'])
                //     ->exists();

                // if ($alreadyRunning) {
                //     \Filament\Notifications\Notification::make()
                //         ->title('Export already in progress')
                //         ->body('Your PDF is still being generated. Check the "Bulk PDF Exports" list for status.')
                //         ->warning()
                //         ->send();
                //     return;
                // }

                $export = \App\Models\PdfExport::create([
                    'user_id' => auth()->id(),
                    'institution_id' => $tenant->id,
                    'status' => 'pending',
                    'ward_id'=> is_object($tenant) ? $tenant->ward_id : null,
                ]);

                \App\Jobs\GenerateBulkChequesPdfJob::dispatch($export->id);

                \Filament\Notifications\Notification::make()
                    ->title('PDF generation started')
                    ->body('You can monitor progress and download your PDF from the "Bulk PDF Exports" list.')
                    ->success()
                    ->send();
            });
    }
}
