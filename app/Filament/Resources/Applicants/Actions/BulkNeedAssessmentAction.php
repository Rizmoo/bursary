<?php

namespace App\Filament\Resources\Applicants\Actions;

use App\Imports\ApplicantsNeedAssessmentImport;
use App\Models\Applicant;
use App\Models\FinancialYear;
use App\Models\Institution;
use App\Support\FinancialYearScope;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class BulkNeedAssessmentAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'bulkNeedAssessment';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Need Assessment')
            ->icon('heroicon-o-clipboard-document-check')
            ->color('info')
            ->modalHeading('Bulk Need Assessment')
            ->modalDescription('Create scores in bulk or import them from an Excel/CSV file.')
            ->modalWidth('xl')
            ->form([
                Select::make('mode')
                    ->label('Mode')
                    ->options([
                        'create' => 'Create / Update in bulk',
                        'import' => 'Import from file',
                    ])
                    ->default('create')
                    ->live()
                    ->required(),

                Select::make('financial_year_id')
                    ->label('Financial Year')
                    ->options(
                        fn () => FinancialYear::query()
                            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
                            ->orderByDesc('start_date')
                            ->pluck('name', 'id')
                    )
                    ->default(function () {
                        $tenantId = filament()->getTenant()?->getKey();

                        return optional(FinancialYearScope::resolveForTenant($tenantId))->getKey();
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('institution_id')
                    ->label('Institution (optional)')
                    ->options(function (callable $get) {
                        $tenantId = filament()->getTenant()?->getKey();
                        $financialYearId = (int) ($get('financial_year_id') ?: 0);

                        return Institution::query()
                            ->when($tenantId, fn ($query) => $query->where('ward_id', $tenantId))
                            ->when($financialYearId > 0, fn ($query) => $query->whereHas('applicants', fn ($q) => $q->where('financial_year_id', $financialYearId)))
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->live(),

                TextInput::make('need_assessment')
                    ->label('Need Assessment Score (0-100)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(fn (callable $get) => $get('mode') === 'create')
                    ->visible(fn (callable $get) => $get('mode') === 'create'),

                Toggle::make('only_empty')
                    ->label('Only set where score is 0')
                    ->default(true)
                    ->visible(fn (callable $get) => $get('mode') === 'create'),

                FileUpload::make('file')
                    ->label('Spreadsheet (xlsx / xls / csv)')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                    ])
                    ->helperText('Expected columns: admission_number, need_assessment')
                    ->storeFiles(false)
                    ->required(fn (callable $get) => $get('mode') === 'import')
                    ->visible(fn (callable $get) => $get('mode') === 'import'),

                Toggle::make('overwrite_existing')
                    ->label('Overwrite existing scores')
                    ->default(false)
                    ->visible(fn (callable $get) => $get('mode') === 'import'),
            ])
            ->action(function (array $data): void {
                $tenant = filament()->getTenant();
                $tenantId = $tenant?->getKey();

                if ($data['mode'] === 'create') {
                    $query = Applicant::query()
                        ->where('ward_id', $tenantId)
                        ->where('financial_year_id', (int) $data['financial_year_id']);

                    if (! empty($data['institution_id'])) {
                        $query->where('institution_id', (int) $data['institution_id']);
                    }

                    if (! empty($data['only_empty'])) {
                        $query->where(function (Builder $builder): void {
                            $builder->whereNull('need_assessment')->orWhere('need_assessment', '<=', 0);
                        });
                    }

                    $updated = $query->update([
                        'need_assessment' => (int) $data['need_assessment'],
                        'updated_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Need assessment updated')
                        ->body("Updated {$updated} applicant(s).")
                        ->success()
                        ->send();

                    return;
                }

                $import = new ApplicantsNeedAssessmentImport(
                    wardId: (int) $tenantId,
                    financialYearId: (int) $data['financial_year_id'],
                    overwriteExisting: (bool) ($data['overwrite_existing'] ?? false),
                );

                Excel::import($import, $data['file']);

                Notification::make()
                    ->title('Need assessment import completed')
                    ->body("Updated {$import->updatedCount} applicant(s). Not found: {$import->notFoundCount}. Skipped: {$import->skippedCount}.")
                    ->success()
                    ->send();
            });
    }
}
