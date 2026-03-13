<?php

namespace App\Filament\Resources\Applicants\Pages;

use App\Filament\Resources\Applicants\Actions\ImportApplicantsAction;
use App\Filament\Resources\Applicants\Actions\QuickAwardApplicantsAction;
use App\Filament\Resources\Applicants\Actions\ExportApplicantsExcelAction;
use App\Filament\Resources\Applicants\Actions\AwardHelperCalculatorAction;
use App\Filament\Resources\Applicants\ApplicantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListApplicants extends ListRecords
{
    protected static string $resource = ApplicantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportApplicantsExcelAction::make(),
            AwardHelperCalculatorAction::make(),
            QuickAwardApplicantsAction::make(),
            ImportApplicantsAction::make(),
            CreateAction::make(),
        ];
    }
}
