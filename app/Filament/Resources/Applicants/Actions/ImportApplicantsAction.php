<?php

namespace App\Filament\Resources\Applicants\Actions;

use App\Imports\ApplicantsImport;
use App\Models\FinancialYear;
use App\Models\InstitutionCategory;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

class ImportApplicantsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'importApplicants';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Import from Excel')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->modalHeading('Import Applicants from Excel')
            ->modalDescription('Upload the standard bursary applicant spreadsheet. Rows with duplicate admission numbers (per financial year) are skipped. New institutions are created automatically under the selected category.')
            ->modalWidth('lg')
            ->form([
                Select::make('financial_year_id')
                    ->label('Financial Year')
                    ->options(
                        fn () => FinancialYear::query()
                            ->when(
                                filament()->getTenant(),
                                fn ($q, $t) => $q->where('ward_id', $t->getKey())
                            )
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('category_id')
                    ->label('Level of Institution')
                    ->options(
                        fn () => InstitutionCategory::query()
                            ->when(
                                filament()->getTenant(),
                                fn ($q, $t) => $q->where('ward_id', $t->getKey())
                            )
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                FileUpload::make('file')
                    ->label('Spreadsheet (xlsx / xls / csv)')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                    ])
                    ->storeFiles(false)
                    ->required(),
            ])
            ->action(function (array $data): void {
                $tenant = filament()->getTenant();

                $import = new ApplicantsImport(
                    wardId:         $tenant->getKey(),
                    financialYearId: (int) $data['financial_year_id'],
                    categoryId:      (int) $data['category_id'],
                );

                Excel::import($import, $data['file']);

                Notification::make()
                    ->title('Import complete')
                    ->body("Imported {$import->importedCount} applicant(s). Skipped {$import->skippedCount} duplicate(s).")
                    ->success()
                    ->send();
            });
    }
}
