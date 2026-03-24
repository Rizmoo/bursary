<?php

namespace App\Filament\App\Pages;

use App\Models\Applicant;
use App\Models\Institution;
use App\Models\User;
use App\Models\Ward;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class CountyKpiDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'County KPI Dashboard';

    protected string $view = 'filament.app.pages.county-kpi-dashboard';

    public function getStats(): array
    {
        $countyId = auth()->user()?->county_id;

        $wardsQuery = Ward::query()->where('county_id', $countyId);
        $wardIds = $wardsQuery->pluck('id');

        return [
            'wards' => $wardIds->count(),
            'ward_users' => User::query()->whereIn('ward_id', $wardIds)->count(),
            'applicants' => Applicant::query()->whereIn('ward_id', $wardIds)->count(),
            'beneficiaries' => Applicant::query()->whereIn('ward_id', $wardIds)->where('amount_awarded', '>', 0)->count(),
            'total_awarded' => (float) Applicant::query()->whereIn('ward_id', $wardIds)->sum('amount_awarded'),
            'institutions' => Institution::query()->whereIn('ward_id', $wardIds)->count(),
        ];
    }
}
