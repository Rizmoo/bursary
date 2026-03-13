<?php

namespace App\Filament\Resources\FinancialYears\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FinancialYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_current')
                            ->label('Active / Current Year')
                            ->columnSpanFull(),
                    ]),

                Section::make('Period')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('start_date')
                            ->required(),
                        DatePicker::make('end_date')
                            ->required()
                            ->afterOrEqual('start_date'),
                    ]),

                Section::make('Financials')
                    ->columns(3)
                    ->schema([
                        TextInput::make('budget')
                            ->numeric()
                            ->prefix('KES')
                            ->required()
                            ->default(0),
                        TextInput::make('opening_balance')
                            ->numeric()
                            ->prefix('KES')
                            ->default(0),
                        TextInput::make('closing_balance')
                            ->numeric()
                            ->prefix('KES')
                            ->default(0),
                        TextInput::make('unutilised_amount')
                            ->numeric()
                            ->prefix('KES')
                            ->default(0),
                    ]),
            ]);
    }
}
