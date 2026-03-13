<?php

namespace App\Filament\Resources\Applicants\Actions;

use App\Models\Applicant;
use App\Models\FinancialYear;
use App\Models\InstitutionCategory;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuickAwardApplicantsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'quickAwardApplicants';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Quick Award')
            ->icon('heroicon-o-currency-dollar')
            ->color('success')
            ->modalHeading('Quick Award Applicants')
            ->modalDescription('Apply flat award amounts by need-assessment bands for a selected institution category and optional vulnerability filters.')
            ->modalWidth('4xl')
            ->form([
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
                    ->required(),

                Select::make('category_id')
                    ->label('Institution Category')
                    ->options(
                        fn () => InstitutionCategory::query()
                            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('orphan_statuses')
                    ->label('Orphan Status Filter')
                    ->options([
                        Applicant::ORPHAN_STATUS_NONE => 'Not Orphan',
                        Applicant::ORPHAN_STATUS_PARTIAL => 'Partial Orphan',
                        Applicant::ORPHAN_STATUS_TOTAL => 'Total Orphan',
                    ])
                    ->multiple()
                    ->searchable()
                    ->helperText('Optional. Leave blank to include all orphan statuses.'),

                Select::make('has_disability_filter')
                    ->label('Applicant Disability Filter')
                    ->options([
                        'any' => 'Any',
                        'yes' => 'Has Disability',
                        'no' => 'No Disability',
                    ])
                    ->default('any')
                    ->required(),

                Select::make('has_disabled_parent_filter')
                    ->label('Disabled Parent Filter')
                    ->options([
                        'any' => 'Any',
                        'yes' => 'Has Disabled Parent',
                        'no' => 'No Disabled Parent',
                    ])
                    ->default('any')
                    ->required(),

                Toggle::make('overwrite_existing_awards')
                    ->label('Overwrite Existing Awards')
                    ->default(false)
                    ->helperText('When off, only applicants with amount awarded <= 0 will be updated.'),

                Toggle::make('exclude_cheque_assigned')
                    ->label('Exclude Applicants Already Assigned to Cheques')
                    ->default(true),

                Repeater::make('award_rules')
                    ->label('Award Rules')
                    ->schema([
                        TextInput::make('min_need')
                            ->label('Min Need Score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('max_need')
                            ->label('Max Need Score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('amount')
                            ->label('Award Amount (KES)')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                    ])
                    ->columns(3)
                    ->default([
                        ['min_need' => 0, 'max_need' => 40, 'amount' => 1500],
                        ['min_need' => 41, 'max_need' => 60, 'amount' => 2000],
                    ])
                    ->minItems(1)
                    ->required(),
            ])
            ->action(function (array $data): void {
                $tenant = filament()->getTenant();

                if (! $tenant) {
                    throw ValidationException::withMessages([
                        'tenant' => 'No tenant context found.',
                    ]);
                }

                $rules = collect($data['award_rules'] ?? [])->values();

                foreach ($rules as $index => $rule) {
                    if ((int) $rule['min_need'] > (int) $rule['max_need']) {
                        throw ValidationException::withMessages([
                            "award_rules.{$index}.max_need" => 'Max Need Score must be greater than or equal to Min Need Score.',
                        ]);
                    }
                }

                $totalUpdated = 0;
                $summary = [];

                DB::transaction(function () use ($data, $rules, $tenant, &$totalUpdated, &$summary): void {
                    $baseQuery = Applicant::query()
                        ->where('ward_id', $tenant->getKey())
                        ->where('financial_year_id', (int) $data['financial_year_id'])
                        ->whereNotNull('admission_number')
                        ->where('admission_number', '!=', '')
                        ->whereHas('institution', fn (Builder $query) => $query->where('category_id', (int) $data['category_id']));

                    if (! empty($data['orphan_statuses'])) {
                        $baseQuery->whereIn('orphan_status', $data['orphan_statuses']);
                    }

                    $this->applyBooleanFilter($baseQuery, 'has_disability', $data['has_disability_filter'] ?? 'any');
                    $this->applyBooleanFilter($baseQuery, 'has_disabled_parent', $data['has_disabled_parent_filter'] ?? 'any');

                    if (! empty($data['exclude_cheque_assigned'])) {
                        $baseQuery->whereDoesntHave('institutionCheques');
                    }

                    foreach ($rules as $rule) {
                        $minNeed = (int) $rule['min_need'];
                        $maxNeed = (int) $rule['max_need'];
                        $amount = (float) $rule['amount'];

                        $query = (clone $baseQuery)->whereBetween('need_assessment', [$minNeed, $maxNeed]);

                        if (empty($data['overwrite_existing_awards'])) {
                            $query->where('amount_awarded', '<=', 0);
                        }

                        $updated = $query->update([
                            'amount_awarded' => $amount,
                            'awarded_at' => now()->toDateString(),
                            'updated_at' => now(),
                        ]);

                        $totalUpdated += $updated;
                        $summary[] = "{$minNeed}-{$maxNeed}: {$updated} applicant(s) @ KES " . number_format($amount, 2);
                    }
                });

                Notification::make()
                    ->title('Quick award completed')
                    ->body("Updated {$totalUpdated} applicant(s).\n" . implode("\n", $summary))
                    ->success()
                    ->send();
            });
    }

    protected function applyBooleanFilter(Builder $query, string $column, string $filter): void
    {
        if ($filter === 'yes') {
            $query->where($column, true);

            return;
        }

        if ($filter === 'no') {
            $query->where($column, false);
        }
    }
}
