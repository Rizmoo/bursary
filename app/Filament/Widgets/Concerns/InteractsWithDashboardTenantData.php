<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\Applicant;
use App\Models\FinancialYear;
use App\Support\FinancialYearScope;
use Illuminate\Database\Eloquent\Builder;

trait InteractsWithDashboardTenantData
{
    protected function getTenantId(): ?int
    {
        return filament()->getTenant()?->getKey();
    }

    protected function getCurrentFinancialYear(): ?FinancialYear
    {
        return FinancialYearScope::resolveForTenant($this->getTenantId());
    }

    protected function getApplicantQuery(?string $filter = null): Builder
    {
        $query = Applicant::query()
            ->with(['institution.category', 'financialYear'])
            ->when($this->getTenantId(), fn (Builder $builder, int $tenantId) => $builder->where('applicants.ward_id', $tenantId));

        $selectedFinancialYearId = $this->getCurrentFinancialYear()?->getKey();

        $query->when($selectedFinancialYearId, fn (Builder $builder) => $builder->where('applicants.financial_year_id', $selectedFinancialYearId));

        return $this->applyFinancialYearFilter($query, $filter);
    }

    protected function getFinancialYearQuery(): Builder
    {
        return FinancialYear::query()
            ->when($this->getTenantId(), fn (Builder $query, int $tenantId) => $query->where('ward_id', $tenantId));
    }

    protected function applyFinancialYearFilter(Builder $query, ?string $filter = null): Builder
    {
        if ($filter !== 'current') {
            return $query;
        }

        $currentFinancialYear = $this->getCurrentFinancialYear();

        if (! $currentFinancialYear) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('applicants.financial_year_id', $currentFinancialYear->getKey());
    }
}
