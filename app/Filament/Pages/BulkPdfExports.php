<?php

namespace App\Filament\Pages;

use App\Models\PdfExport;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;

class BulkPdfExports extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-arrow-down';
    protected string $view = 'filament.pages.bulk-pdf-exports';
    protected static ?string $title = 'Bulk PDF Exports';
    protected static ?string $navigationLabel = 'Bulk PDF Exports';
    protected static ?int $navigationSort = 90;

    public function getTableQuery(): Builder
    {
        $tenant = filament()->getTenant();
        return PdfExport::query()
            ->where('user_id', auth()->id())
            ->when($tenant, fn ($q) => $q->where('institution_id', is_object($tenant) ? $tenant->id : $tenant))
            ->latest();
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('created_at')->label('Requested At')->dateTime('Y-m-d H:i'),
            BadgeColumn::make('status')
                ->formatStateUsing(fn ($state) => [
                    'pending' => 'Pending',
                    'processing' => 'Processing',
                    'complete' => 'Ready',
                    'failed' => 'Failed',
                ][$state] ?? $state)
                ->colors([
                    'pending' => 'warning',
                    'processing' => 'info',
                    'complete' => 'success',
                    'failed' => 'danger',
                ]),
            TextColumn::make('filename')->label('File Name')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('expires_at')->label('Expires')->dateTime('Y-m-d H:i')->toggleable(isToggledHiddenByDefault: true),
            \Filament\Tables\Columns\TextColumn::make('download')
                ->label('Download')
                ->formatStateUsing(function ($state, $record) {
                     $url = route('pdf.download', ['path' => $record->storage_path, 'expires' => $record->expires_at?->timestamp]);
                        return '<a href="' . $url . '" target="_blank" style="color: #2563eb; text-decoration: underline;">Download</a>';
                })
                ->html(),
        ];
    }

    public function getTableActions(): array
    {
        return [];
    }
}
