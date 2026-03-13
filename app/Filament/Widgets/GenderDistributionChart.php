<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithDashboardTenantData;
use Filament\Widgets\ChartWidget;

class GenderDistributionChart extends ChartWidget
{
    use InteractsWithDashboardTenantData;

    protected ?string $heading = 'Female vs Male';

    protected ?string $description = 'Applicant distribution by gender.';

    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'doughnut';
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
        $query = $this->getApplicantQuery($this->filter ?? 'all');

        $female = (clone $query)->where('gender', 'female')->count();
        $male = (clone $query)->where('gender', 'male')->count();
        $unspecified = (clone $query)->whereNotIn('gender', ['female', 'male'])->count();

        return [
            'datasets' => [[
                'label' => 'Applicants',
                'data' => [$female, $male, $unspecified],
                'backgroundColor' => ['#ec4899', '#3b82f6', '#94a3b8'],
            ]],
            'labels' => ['Female', 'Male', 'Unspecified'],
        ];
    }
}
