<?php

namespace App\Filament\Pages;

use App\Models\FinancialYear;
use App\Services\QuarterlyInstitutionLevelReportService;
use App\Support\FinancialYearScope;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use UnitEnum;

class QuarterlyReports extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Quarterly Reports';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.quarterly-reports';

    public ?int $financialYearId = null;

    public int $quarter = 1;

    public string $openingBalance = '0';

    public string $moneyDeposited = '0';

    public string $administrationCost = '0';

    public string $bankCharges = '0';

    public function mount(): void
    {
        $this->financialYearId = optional(FinancialYearScope::resolveForTenant(filament()->getTenant()?->getKey()))->getKey()
            ?? $this->getFinancialYearOptions()->keys()->first();
        $this->hydrateFinancialInputs();
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
     * @return array{period_start: \Carbon\CarbonInterface|null, period_end: \Carbon\CarbonInterface|null, rows: Collection<int, array{name: string, beneficiaries: int, total_awarded: float}>, totals: array{beneficiaries: int, total_awarded: float}}
     */
    public function getReportData(): array
    {
        if (blank($this->financialYearId)) {
            return [
                'period_start' => null,
                'period_end' => null,
                'rows' => collect(),
                'totals' => [
                    'beneficiaries' => 0,
                    'total_awarded' => 0,
                ],
            ];
        }

        $financialYear = FinancialYear::query()
            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
            ->findOrFail($this->financialYearId);

        return app(QuarterlyInstitutionLevelReportService::class)->generate($financialYear, $this->quarter);
    }

    public function updatedFinancialYearId(): void
    {
        $this->hydrateFinancialInputs();
    }

    public function getExportUrl(): ?string
    {
        if (blank($this->financialYearId) || ! filament()->getTenant() || ! Route::has('quarterly-reports.export')) {
            return null;
        }

        return route('quarterly-reports.export', [
            'tenant' => filament()->getTenant(),
            'financial_year_id' => $this->financialYearId,
            'quarter' => $this->quarter,
            'opening_balance' => $this->openingBalance,
            'money_deposited' => $this->moneyDeposited,
            'administration_cost' => $this->administrationCost,
            'bank_charges' => $this->bankCharges,
        ]);
    }

    protected function hydrateFinancialInputs(): void
    {
        if (blank($this->financialYearId)) {
            return;
        }

        $financialYear = FinancialYear::query()
            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
            ->find($this->financialYearId);

        if (! $financialYear) {
            return;
        }

        $this->openingBalance = (string) ((float) $financialYear->opening_balance);
        $this->moneyDeposited = '0';
        $this->administrationCost = '0';
        $this->bankCharges = '0';
    }
}
