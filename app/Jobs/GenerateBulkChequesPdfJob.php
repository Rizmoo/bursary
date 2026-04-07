<?php

namespace App\Jobs;

use App\Models\Institution;
use App\Models\InstitutionCheque;
use App\Models\PdfExport;
use App\Mail\BulkPdfReady;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class GenerateBulkChequesPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 2;

    public function __construct(
        private readonly int $exportId
    ) {}

    public function handle(): void
    {
        Log::info('Starting bulk cheque PDF generation', ['export_id' => $this->exportId]);
        $export = PdfExport::findOrFail($this->exportId);
        $export->update(['status' => 'processing']);

        try {
            $tenant = Institution::findOrFail($export->institution_id);

            $filename = "cheques-bulk-{$export->institution_id}-" . now()->format('YmdHis') . '.pdf';
            $storagePath = "pdf-exports/{$filename}";

            // Cursor to avoid loading all records into memory
            $cheques = InstitutionCheque::query()
                ->where('institution_id', $export->institution_id)
                ->with([
                    'institution',
                    'financialYear',
                    'applicants' => fn ($q) => $q->orderBy('last_name')->orderBy('first_name'),
                ])
                ->cursor();

            $pdf = Pdf::loadView('pdfs.institution-cheques-bulk', [
                'cheques' => $cheques,
                'tenant'  => $tenant,
            ])->setPaper('a4', 'portrait');

            // Write to disk
            Storage::put($storagePath, $pdf->output());

            // Generate a signed, expiring download URL (1 hour) for immediate use if needed,
            // but we'll primarily use the storage_path for the history table.
            $signedUrl = URL::temporarySignedRoute(
                'pdf.download',
                now()->addHour(),
                ['path' => $storagePath]
            );

            $export->update([
                'status' => 'complete',
                'storage_path' => $storagePath,
                'download_url' => $signedUrl,
                'filename' => $filename,
                'expires_at' => now()->addHour(),
            ]);

        } catch (\Throwable $e) {
            $export->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
            Log::error('Bulk cheque PDF generation failed', [
                'export_id' => $this->exportId,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        $export = PdfExport::find($this->exportId);
        if ($export) {
            $export->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
