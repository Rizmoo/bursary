<?php

namespace App\Filament\Resources\PdfExports;

use App\Filament\Resources\PdfExports\Pages\ManagePdfExports;
use App\Models\PdfExport;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PdfExportResource extends Resource
{
    protected static ?string $model = PdfExport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'file name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('institution_id')
                    ->required()
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('download_url')
                    ->url(),
                TextInput::make('filename'),
                DateTimePicker::make('expires_at'),
                Textarea::make('error')
                    ->columnSpanFull(),
                TextInput::make('storage_path'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file name')
            ->columns([
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('institution_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('download_url')
                    ->searchable(),
                TextColumn::make('filename')
                    ->searchable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('storage_path')
                    ->searchable(),
                TextColumn::make('download')
                    ->label('Download')
                    ->formatStateUsing(function ($state, $record) {
                        if (filled($record->storage_path) && in_array($record->status, ['complete', 'completed', 'ready'])) {
                            $url = route('pdf.download', ['path' => $record->storage_path, 'expires' => $record->expires_at?->timestamp]);
                            return '<a href="' . $url . '" target="_blank" style="color: #2563eb; text-decoration: underline;">Download</a>';
                        }
                        return '';
                    })
                    ->html(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePdfExports::route('/'),
        ];
    }
}