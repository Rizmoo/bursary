<?php

namespace App\Filament\Resources\Institutions\Schemas;

use App\Models\Institution;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InstitutionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('code'),
                        TextEntry::make('category.name')->label('Category'),
                        TextEntry::make('address'),
                    ]),

                Section::make('Contact Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('contact_person'),
                        TextEntry::make('contact_email'),
                        TextEntry::make('contact_phone'),
                    ]),

                Section::make('Bursary Summary')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('beneficiaries_count')
                            ->label('Beneficiaries')
                            ->state(fn (Institution $record): int => $record->applicants()->beneficiaries()->count()),
                        TextEntry::make('total_awarded')
                            ->label('Total Awarded')
                            ->state(fn (Institution $record): float => (float) $record->applicants()->beneficiaries()->sum('amount_awarded'))
                            ->money('KES'),
                        TextEntry::make('cheques_count')
                            ->label('Cheques Issued')
                            ->state(fn (Institution $record): int => $record->institutionCheques()->count()),
                    ]),
            ]);
    }
}
