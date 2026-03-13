<?php

namespace App\Filament\Resources\Applicants\Actions;

use App\Models\FinancialYear;
use App\Models\InstitutionCategory;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class ExportApplicantsExcelAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'exportApplicantsExcel';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Export')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->modalHeading('Export Applicants / Beneficiaries')
            ->modalDescription('Choose export type and optional filters. Use scope "All" to export all records in the tenant.')
            ->modalWidth('3xl')
            ->form([
                Select::make('export_type')
                    ->label('Export Type')
                    ->options([
                        'applicants' => 'Applicants',
                        'beneficiaries' => 'Beneficiaries',
                    ])
                    ->default('beneficiaries')
                    ->required(),

                Select::make('scope')
                    ->label('Scope')
                    ->options([
                        'all' => 'All',
                        'filtered' => 'By Filters',
                    ])
                    ->default('all')
                    ->required()
                    ->live(),

                Select::make('financial_year_id')
                    ->label('Financial Year')
                    ->options(
                        fn () => FinancialYear::query()
                            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
                            ->orderByDesc('start_date')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->visible(fn (callable $get): bool => $get('scope') === 'filtered'),

                Select::make('institution_category_id')
                    ->label('Institution Category')
                    ->options(
                        fn () => InstitutionCategory::query()
                            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->visible(fn (callable $get): bool => $get('scope') === 'filtered'),

                TextInput::make('need_min')
                    ->label('Need Score Min')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->visible(fn (callable $get): bool => $get('scope') === 'filtered'),

                TextInput::make('need_max')
                    ->label('Need Score Max')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->visible(fn (callable $get): bool => $get('scope') === 'filtered'),

                Toggle::make('orphans_only')
                    ->label('Only Orphans')
                    ->default(false)
                    ->visible(fn (callable $get): bool => $get('scope') === 'filtered'),

                Select::make('has_disability_filter')
                    ->label('Applicant Disability')
                    ->options([
                        'any' => 'Any',
                        'yes' => 'Has Disability',
                        'no' => 'No Disability',
                    ])
                    ->default('any')
                    ->visible(fn (callable $get): bool => $get('scope') === 'filtered'),

                Select::make('has_disabled_parent_filter')
                    ->label('Disabled Parent')
                    ->options([
                        'any' => 'Any',
                        'yes' => 'Has Disabled Parent',
                        'no' => 'No Disabled Parent',
                    ])
                    ->default('any')
                    ->visible(fn (callable $get): bool => $get('scope') === 'filtered'),

                Toggle::make('beneficiaries_only')
                    ->label('Applicants export: only beneficiaries')
                    ->default(false)
                    ->visible(fn (callable $get): bool => $get('scope') === 'filtered' && $get('export_type') === 'applicants'),
            ])
            ->action(function (array $data) {
                $tenant = filament()->getTenant();

                $params = [
                    'tenant' => $tenant,
                    'export_type' => $data['export_type'] ?? 'beneficiaries',
                    'scope' => $data['scope'] ?? 'all',
                ];

                if (($data['scope'] ?? 'all') === 'filtered') {
                    $params = [
                        ...$params,
                        'financial_year_id' => $data['financial_year_id'] ?? null,
                        'institution_category_id' => $data['institution_category_id'] ?? null,
                        'need_min' => $data['need_min'] ?? null,
                        'need_max' => $data['need_max'] ?? null,
                        'orphans_only' => ! empty($data['orphans_only']) ? 1 : 0,
                        'has_disability_filter' => $data['has_disability_filter'] ?? 'any',
                        'has_disabled_parent_filter' => $data['has_disabled_parent_filter'] ?? 'any',
                        'beneficiaries_only' => ! empty($data['beneficiaries_only']) ? 1 : 0,
                    ];
                }

                return redirect()->route('applicants.filtered.excel', $params);
            });
    }
}
