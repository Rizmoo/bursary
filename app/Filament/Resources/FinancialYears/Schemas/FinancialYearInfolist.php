<?php

namespace App\Filament\Resources\FinancialYears\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FinancialYearInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        IconEntry::make('is_current')
                            ->label('Active / Current Year')
                            ->boolean(),
                    ]),

                Section::make('Period')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('start_date')->date(),
                        TextEntry::make('end_date')->date(),
                    ]),

                Section::make('Financials')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('budget')->money('KES'),
                        TextEntry::make('opening_balance')->money('KES'),
                        TextEntry::make('closing_balance')->money('KES'),
                        TextEntry::make('unutilised_amount')->money('KES'),
                    ]),
            ]);
    }
}
