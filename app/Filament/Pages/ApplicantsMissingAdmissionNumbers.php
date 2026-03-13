<?php

namespace App\Filament\Pages;

use App\Models\Applicant;
use App\Models\FinancialYear;
use App\Support\FinancialYearScope;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

class ApplicantsMissingAdmissionNumbers extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Missing Admission Numbers';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.applicants-missing-admission-numbers';

    public ?int $financialYearId = null;

    public function mount(): void
    {
        $this->financialYearId = optional(FinancialYearScope::resolveForTenant(filament()->getTenant()?->getKey()))->getKey()
            ?? $this->getFinancialYearOptions()->keys()->first();
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
     * @return Collection<int, Applicant>
     */
    public function getRows(): Collection
    {
        return Applicant::query()
            ->with(['institution.category', 'financialYear'])
            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
            ->when($this->financialYearId, fn ($query) => $query->where('financial_year_id', $this->financialYearId))
            ->where(function ($query): void {
                $query->whereNull('admission_number')
                    ->orWhere('admission_number', '');
            })
            ->orderBy('institution_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    public function getAwardedWithoutAdmissionCount(): int
    {
        return Applicant::query()
            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
            ->when($this->financialYearId, fn ($query) => $query->where('financial_year_id', $this->financialYearId))
            ->where(function ($query): void {
                $query->whereNull('admission_number')
                    ->orWhere('admission_number', '');
            })
            ->where('amount_awarded', '>', 0)
            ->count();
    }
}
