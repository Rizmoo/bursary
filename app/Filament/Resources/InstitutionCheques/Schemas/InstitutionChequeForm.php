<?php

namespace App\Filament\Resources\InstitutionCheques\Schemas;

use App\Models\InstitutionCheque;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InstitutionChequeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cheque Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('cheque_number')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('cheque_date')
                            ->required(),
                        TextInput::make('institution.name')
                            ->label('Institution')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('financialYear.name')
                            ->label('Financial Year')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('status')
                            ->options(InstitutionCheque::getStatuses())
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                Section::make('Financials')
                    ->columns(3)
                    ->schema([
                        TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('KES')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('returned_amount')
                            ->numeric()
                            ->prefix('KES')
                            ->disabled()
                            ->dehydrated(false),
                        DatePicker::make('returned_at')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                Section::make('Remarks')
                    ->schema([
                        Textarea::make('remarks')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
