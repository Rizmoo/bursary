<?php

namespace App\Services;

use App\Models\FinancialYear;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class QuarterlyInstitutionLevelReportService
{
    /**
     * @return array{period_start: Carbon, period_end: Carbon, rows: Collection<int, array{name: string, beneficiaries: int, total_awarded: float}>, totals: array{beneficiaries: int, total_awarded: float}}
     */
    public function generate(FinancialYear $financialYear, int $quarter): array
    {
        if (($quarter < 1) || ($quarter > 4)) {
            throw new InvalidArgumentException('Quarter must be between 1 and 4.');
        }

        $periodStart = Carbon::parse($financialYear->start_date)->addMonths(($quarter - 1) * 3)->startOfDay();
        $periodEnd = Carbon::parse($financialYear->start_date)->addMonths($quarter * 3)->subDay()->endOfDay();
        $financialYearEnd = Carbon::parse($financialYear->end_date)->endOfDay();

        if ($periodEnd->greaterThan($financialYearEnd)) {
            $periodEnd = $financialYearEnd;
        }

        $rows = DB::table('institution_categories')
            ->leftJoin('institutions', 'institution_categories.id', '=', 'institutions.category_id')
            ->leftJoin('applicants', function ($join) use ($financialYear, $periodStart, $periodEnd): void {
                $join->on('institutions.id', '=', 'applicants.institution_id')
                    ->where('applicants.financial_year_id', '=', $financialYear->getKey())
                    ->where('applicants.amount_awarded', '>', 0)
                    ->whereBetween('applicants.awarded_at', [$periodStart->toDateString(), $periodEnd->toDateString()]);
            })
            ->where('institution_categories.ward_id', $financialYear->ward_id)
            ->groupBy('institution_categories.id', 'institution_categories.name')
            ->orderBy('institution_categories.name')
            ->get([
                'institution_categories.name',
                DB::raw('count(applicants.id) as beneficiaries'),
                DB::raw('coalesce(sum(applicants.amount_awarded), 0) as total_awarded'),
            ])
            ->map(fn ($row): array => [
                'name' => $row->name,
                'beneficiaries' => (int) $row->beneficiaries,
                'total_awarded' => (float) $row->total_awarded,
            ]);

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'rows' => $rows,
            'totals' => [
                'beneficiaries' => $rows->sum('beneficiaries'),
                'total_awarded' => (float) $rows->sum('total_awarded'),
            ],
        ];
    }

    /**
     * @param  array{opening_balance: float, money_deposited: float, administration_cost: float, bank_charges: float}  $inputs
     * @return array<string, mixed>
     */
    public function buildWorkbookData(FinancialYear $financialYear, int $quarter, array $inputs): array
    {
        $report = $this->generate($financialYear, $quarter);
        $categoryBreakdown = $this->buildCategoryBreakdown($report['rows']);
        $totalExpenditure = (float) $report['totals']['total_awarded'] + $inputs['administration_cost'] + $inputs['bank_charges'];
        $amountAvailable = $inputs['opening_balance'] + $inputs['money_deposited'];

        return [
            'tenant_name' => $financialYear->ward->name,
            'financial_year_name' => $financialYear->name,
            'quarter' => $quarter,
            'quarter_label' => $this->getQuarterLabel($quarter),
            'period_start' => $report['period_start'],
            'period_end' => $report['period_end'],
            'rows' => $report['rows'],
            'totals' => $report['totals'],
            'category_breakdown' => $categoryBreakdown,
            'opening_balance' => $inputs['opening_balance'],
            'money_deposited' => $inputs['money_deposited'],
            'administration_cost' => $inputs['administration_cost'],
            'bank_charges' => $inputs['bank_charges'],
            'amount_available' => $amountAvailable,
            'total_expenditure' => $totalExpenditure,
            'closing_balance' => $amountAvailable - $totalExpenditure,
        ];
    }

    /**
     * @param  Collection<int, array{name: string, beneficiaries: int, total_awarded: float}>  $rows
     * @return array<string, array{label: string, beneficiaries: int, total_awarded: float}>
     */
    protected function buildCategoryBreakdown(Collection $rows): array
    {
        $breakdown = [
            'universities' => [
                'label' => 'Universities',
                'beneficiaries' => 0,
                'total_awarded' => 0,
            ],
            'tertiary_colleges' => [
                'label' => 'Tertiary Colleges',
                'beneficiaries' => 0,
                'total_awarded' => 0,
            ],
            'secondary' => [
                'label' => 'Secondary Schools',
                'beneficiaries' => 0,
                'total_awarded' => 0,
            ],
        ];

        foreach ($rows as $row) {
            $normalized = str($row['name'])->lower()->value();

            $key = match (true) {
                str_contains($normalized, 'university') => 'universities',
                str_contains($normalized, 'tertiary') || str_contains($normalized, 'college') => 'tertiary_colleges',
                str_contains($normalized, 'secondary') => 'secondary',
                default => null,
            };

            if (! $key) {
                continue;
            }

            $breakdown[$key]['beneficiaries'] += $row['beneficiaries'];
            $breakdown[$key]['total_awarded'] += $row['total_awarded'];
        }

        return $breakdown;
    }

    protected function getQuarterLabel(int $quarter): string
    {
        return match ($quarter) {
            1 => '1st',
            2 => '2nd',
            3 => '3rd',
            default => '4th',
        };
    }
}
