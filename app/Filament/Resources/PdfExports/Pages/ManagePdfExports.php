<?php

namespace App\Filament\Resources\PdfExports\Pages;

use App\Filament\Resources\PdfExports\PdfExportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePdfExports extends ManageRecords
{
    protected static string $resource = PdfExportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
