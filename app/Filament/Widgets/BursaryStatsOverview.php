<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithDashboardTenantData;
use App\Models\Applicant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class BursaryStatsOverview extends StatsOverviewWidget
{
    use InteractsWithDashboardTenantData;

    protected ?string $heading = 'Bursary Overview';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $financialYears = $this->getFinancialYearQuery()
            ->orderBy('start_date')
            ->get();

        $budgetTrend = $financialYears
            ->map(fn ($financialYear): float => (float) $financialYear->budget)
            ->values()
            ->all();

        $awardTrend = $financialYears
            ->map(function ($financialYear): float {
                return (float) Applicant::query()
                    ->where('ward_id', $financialYear->ward_id)
                    ->where('financial_year_id', $financialYear->id)
                    ->sum('amount_awarded');
            })
            ->values()
            ->all();

        $totalBudget = (float) $financialYears->sum('budget');
        $amountAwarded = (float) $this->getApplicantQuery()->sum('amount_awarded');
        $beneficiaries = $this->getApplicantQuery()->where('amount_awarded', '>', 0)->count();
        $remainingBudget = $totalBudget - $amountAwarded;
        $currentFinancialYear = $this->getCurrentFinancialYear();

        return [
            Stat::make('Budget', 'KES ' . number_format($totalBudget, 2))
                ->description($currentFinancialYear ? 'Current FY: ' . $currentFinancialYear->name : 'All financial years')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('primary')
                ->chart($budgetTrend === [] ? [0] : $budgetTrend),
            Stat::make('Amount Awarded', 'KES ' . number_format($amountAwarded, 2))
                ->description('Total amount awarded to applicants')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success')
                ->chart($awardTrend === [] ? [0] : $awardTrend),
            Stat::make('Beneficiaries', number_format($beneficiaries))
                ->description('Applicants awarded above KES 0')
                ->descriptionIcon('heroicon-o-users')
                ->color('warning')
                ->chart($this->buildBeneficiaryTrend()),
            Stat::make('Remaining Budget', 'KES ' . number_format($remainingBudget, 2))
                ->description('Budget minus awarded amount')
                ->descriptionIcon('heroicon-o-scale')
                ->color($remainingBudget < 0 ? 'danger' : 'info')
                ->chart($this->buildRemainingBudgetTrend($financialYears->pluck('id')->all(), $budgetTrend, $awardTrend)),
        ];
    }

    /**
     * @return array<float>
     */
    protected function buildBeneficiaryTrend(): array
    {
        $trend = $this->getFinancialYearQuery()
            ->orderBy('start_date')
            ->get()
            ->map(function ($financialYear): float {
                return (float) Applicant::query()
                    ->where('ward_id', $financialYear->ward_id)
                    ->where('financial_year_id', $financialYear->id)
                    ->where('amount_awarded', '>', 0)
                    ->count();
            })
            ->values()
            ->all();

        return $trend === [] ? [0] : $trend;
    }

    /**
     * @param  array<int>  $financialYearIds
     * @param  array<float>  $budgetTrend
     * @param  array<float>  $awardTrend
     * @return array<float>
     */
    protected function buildRemainingBudgetTrend(array $financialYearIds, array $budgetTrend, array $awardTrend): array
    {
        if ($financialYearIds === []) {
            return [0];
        }

        $trend = [];

        foreach ($financialYearIds as $index => $financialYearId) {
            $trend[] = ($budgetTrend[$index] ?? 0) - ($awardTrend[$index] ?? 0);
        }

        return $trend;
    }
}
