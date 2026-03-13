<?php

namespace App\Filament\Pages;

use App\Models\BankReconciliation;
use App\Models\BankReconciliationItem;
use App\Models\FinancialYear;
use App\Services\BankReconciliationService;
use App\Support\FinancialYearScope;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use UnitEnum;

class BankReconciliationPage extends Page
{
    use WithFileUploads;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-scale';

    protected static string | UnitEnum | null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Bank Reconciliation';

    protected static ?string $title = 'Bank Statement Reconciliation';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.bank-reconciliation';

    public ?int $financialYearId = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $statementFile = null;

    public ?int $reconciliationId = null;

    public bool $isUploading = false;

    public function mount(): void
    {
        $this->financialYearId = optional(FinancialYearScope::resolveForTenant(filament()->getTenant()?->getKey()))->getKey();

        // Load the latest draft reconciliation if one exists
        $latest = BankReconciliation::query()
            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
            ->when($this->financialYearId, fn ($query) => $query->where('financial_year_id', $this->financialYearId))
            ->where('status', BankReconciliation::STATUS_DRAFT)
            ->latest()
            ->first();

        if ($latest) {
            $this->reconciliationId = $latest->id;
        }
    }

    /**
     * @return Collection<int, string>
     */
    public function getFinancialYearOptions(): Collection
    {
        return FinancialYear::query()
            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
            ->orderByDesc('start_date')
            ->pluck('name', 'id');
    }

    /**
     * Upload and parse the bank statement PDF.
     */
    public function uploadStatement(): void
    {
        if (! $this->statementFile) {
            Notification::make()
                ->title('Please select a PDF file')
                ->danger()
                ->send();

            return;
        }

        $ward = filament()->getTenant();
        if (! $ward) {
            return;
        }

        try {
            $path = $this->statementFile->store('bank-statements', 'local');

            $service = app(BankReconciliationService::class);
            $reconciliation = $service->createFromUpload($ward, $path, $this->financialYearId);

            $this->reconciliationId = $reconciliation->id;
            $this->statementFile = null;

            Notification::make()
                ->title('Bank statement parsed successfully')
                ->body("Found {$reconciliation->items->count()} transactions")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Failed to parse bank statement')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Apply the reconciliation to update cheque statuses.
     */
    public function applyReconciliation(): void
    {
        $reconciliation = $this->getReconciliation();
        if (! $reconciliation) {
            return;
        }

        try {
            $service = app(BankReconciliationService::class);
            $service->apply($reconciliation);

            Notification::make()
                ->title('Reconciliation applied successfully')
                ->body('Cheque statuses have been updated based on the bank statement.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Failed to apply reconciliation')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Discard the current draft reconciliation.
     */
    public function discardReconciliation(): void
    {
        $reconciliation = $this->getReconciliation();
        if (! $reconciliation || ! $reconciliation->isDraft()) {
            return;
        }

        // Delete the uploaded file
        if ($reconciliation->file_path) {
            Storage::disk('local')->delete($reconciliation->file_path);
        }

        $reconciliation->delete();
        $this->reconciliationId = null;

        Notification::make()
            ->title('Reconciliation discarded')
            ->success()
            ->send();
    }

    /**
     * Load a previous reconciliation for review.
     */
    public function loadReconciliation(int $id): void
    {
        $reconciliation = BankReconciliation::query()
            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
            ->find($id);

        if ($reconciliation) {
            $this->reconciliationId = $reconciliation->id;
        }
    }

    /**
     * Get the current reconciliation instance.
     */
    public function getReconciliation(): ?BankReconciliation
    {
        if (! $this->reconciliationId) {
            return null;
        }

        return BankReconciliation::with(['items.institutionCheque.institution'])
            ->find($this->reconciliationId);
    }

    /**
     * Get summary data for the current reconciliation.
     */
    public function getSummaryData(): ?array
    {
        $reconciliation = $this->getReconciliation();
        if (! $reconciliation) {
            return null;
        }

        return app(BankReconciliationService::class)->getSummary($reconciliation);
    }

    /**
     * Get recent reconciliation history.
     *
     * @return Collection<int, BankReconciliation>
     */
    public function getReconciliationHistory(): Collection
    {
        return BankReconciliation::query()
            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
            ->when($this->financialYearId, fn ($query) => $query->where('financial_year_id', $this->financialYearId))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }
}
