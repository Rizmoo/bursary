<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithDashboardTenantData;
use App\Models\InstitutionCategory;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

class InstitutionCategoryDistributionChart extends ChartWidget
{
    use InteractsWithDashboardTenantData;

    protected ?string $heading = 'Institution Category Distribution';

    protected ?string $description = 'Applicants grouped by institution category.';

    protected static ?int $sort = 3;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'all' => 'All financial years',
            'current' => 'Current financial year',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter ?? 'all';

        $categories = InstitutionCategory::query()
            ->when($this->getTenantId(), fn ($query, int $tenantId) => $query->where('ward_id', $tenantId))
            ->orderBy('name')
            ->get();

        $applicantCounts = $this->getApplicantQuery($filter)
            ->join('institutions', 'applicants.institution_id', '=', 'institutions.id')
            ->join('institution_categories', 'institutions.category_id', '=', 'institution_categories.id')
            ->selectRaw('institution_categories.id as category_id, count(*) as aggregate')
            ->groupBy('institution_categories.id')
            ->pluck('aggregate', 'category_id');

        $data = $categories
            ->map(fn (InstitutionCategory $category): int => (int) ($applicantCounts[$category->id] ?? 0));

        return [
            'datasets' => [[
                'label' => 'Applicants',
                'data' => $data->all(),
                'backgroundColor' => '#f59e0b',
                'borderColor' => '#d97706',
            ]],
            'labels' => $categories->pluck('name')->all(),
        ];
    }
}
