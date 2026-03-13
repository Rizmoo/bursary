<?php

namespace App\Filament\Resources\Institutions\Pages;

use App\Filament\Resources\Institutions\InstitutionResource;
use App\Filament\Pages\InstitutionDuplicates;
use App\Models\FinancialYear;
use App\Models\Institution;
use App\Models\InstitutionCategory;
use App\Services\InstitutionChequeService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ListInstitutions extends ListRecords
{
    protected static string $resource = InstitutionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('duplicateInstitutions')
                ->label('Find Duplicates')
                ->icon('heroicon-o-squares-2x2')
                ->color('warning')
                ->url(fn (): string => InstitutionDuplicates::getUrl()),
            Action::make('exportChequeWritingTemplate')
                ->label('Export Cheque Writing Sheet')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->modalHeading('Export Institutions for Cheque Writing')
                ->form([
                    Select::make('financial_year_id')
                        ->label('Financial Year')
                        ->options(
                            fn () => FinancialYear::query()
                                ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
                                ->orderByDesc('start_date')
                                ->pluck('name', 'id')
                        )
                        ->required()
                        ->searchable()
                        ->preload(),
                    Select::make('institution_category_id')
                        ->label('Institution Category')
                        ->options(
                            fn () => InstitutionCategory::query()
                                ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->preload(),
                    TextInput::make('need_min')
                        ->label('Need Score Min')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100),
                    TextInput::make('need_max')
                        ->label('Need Score Max')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100),
                    Select::make('orphans_only')
                        ->label('Only Orphans')
                        ->options([
                            '0' => 'No',
                            '1' => 'Yes',
                        ])
                        ->default('0')
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect()->route('institutions.cheque-writing-template', [
                        'tenant' => filament()->getTenant(),
                        'financial_year_id' => $data['financial_year_id'],
                        'institution_category_id' => $data['institution_category_id'] ?? null,
                        'need_min' => $data['need_min'] ?? null,
                        'need_max' => $data['need_max'] ?? null,
                        'orphans_only' => (int) ($data['orphans_only'] ?? 0),
                    ]);
                }),
            Action::make('importChequeNumbers')
                ->label('Import Cheque Numbers')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import cheque numbers from filled sheet')
                ->modalDescription('Upload the filled cheque writing sheet with Institution ID and Cheque Number columns. A cheque will be created for each institution row with a cheque number.')
                ->form([
                    Select::make('financial_year_id')
                        ->label('Financial Year')
                        ->options(
                            fn () => FinancialYear::query()
                                ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
                                ->orderByDesc('start_date')
                                ->pluck('name', 'id')
                        )
                        ->required()
                        ->searchable()
                        ->preload(),
                    DatePicker::make('cheque_date')
                        ->label('Cheque Date')
                        ->default(now())
                        ->required(),
                    FileUpload::make('sheet')
                        ->label('Filled Cheque Sheet (Excel)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->directory('imports/cheque-writing')
                        ->disk('local')
                        ->required(),
                ])
                ->action(function (array $data, InstitutionChequeService $service): void {
                    $tenant = filament()->getTenant();
                    $filePath = Storage::disk('local')->path($data['sheet']);
                    $spreadsheet = IOFactory::load($filePath);
                    $rows = $spreadsheet->getActiveSheet()->toArray();

                    $header = array_map(fn ($v) => strtolower(trim((string) $v)), $rows[0] ?? []);
                    $institutionIdIndex = array_search('institution id', $header, true);
                    $institutionNameIndex = array_search('institution name', $header, true);
                    $chequeNumberIndex = array_search('cheque number', $header, true);

                    if ($chequeNumberIndex === false || ($institutionIdIndex === false && $institutionNameIndex === false)) {
                        throw new \RuntimeException('Invalid template. Required columns: Institution ID or Institution Name, and Cheque Number.');
                    }

                    $created = 0;
                    $skipped = 0;
                    $errors = [];

                    foreach (array_slice($rows, 1) as $i => $row) {
                        $line = $i + 2;
                        $chequeNumber = trim((string) ($row[$chequeNumberIndex] ?? ''));
                        if ($chequeNumber === '') {
                            continue;
                        }

                        $institution = null;
                        if ($institutionIdIndex !== false) {
                            $institutionId = (int) ($row[$institutionIdIndex] ?? 0);
                            if ($institutionId > 0) {
                                $institution = Institution::query()
                                    ->where('ward_id', $tenant->getKey())
                                    ->find($institutionId);
                            }
                        }

                        if (! $institution && $institutionNameIndex !== false) {
                            $institutionName = trim((string) ($row[$institutionNameIndex] ?? ''));
                            if ($institutionName !== '') {
                                $institution = Institution::query()
                                    ->where('ward_id', $tenant->getKey())
                                    ->where('name', $institutionName)
                                    ->first();
                            }
                        }

                        if (! $institution) {
                            $skipped++;
                            $errors[] = "Line {$line}: Institution not found.";
                            continue;
                        }

                        $applicantIds = $institution->applicants()
                            ->where('financial_year_id', (int) $data['financial_year_id'])
                            ->where('amount_awarded', '>', 0)
                            ->whereNotNull('admission_number')
                            ->where('admission_number', '!=', '')
                            ->whereDoesntHave('institutionCheques')
                            ->pluck('id')
                            ->all();

                        if ($applicantIds === []) {
                            $skipped++;
                            $errors[] = "Line {$line}: No eligible beneficiaries for {$institution->name}.";
                            continue;
                        }

                        try {
                            $service->createForInstitution(
                                institution: $institution,
                                financialYearId: (int) $data['financial_year_id'],
                                applicantIds: $applicantIds,
                                attributes: [
                                    'cheque_number' => $chequeNumber,
                                    'cheque_date' => $data['cheque_date'],
                                    'remarks' => 'Imported from cheque writing sheet',
                                ],
                            );

                            $created++;
                        } catch (Throwable $e) {
                            $skipped++;
                            $errors[] = "Line {$line}: {$e->getMessage()}";
                        }
                    }

                    Storage::disk('local')->delete($data['sheet']);

                    $body = "Created {$created} cheque(s). Skipped {$skipped} row(s).";
                    if (! empty($errors)) {
                        $body .= "\n" . collect($errors)->take(5)->implode("\n");
                    }

                    Notification::make()
                        ->title('Cheque import completed')
                        ->body($body)
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
