<?php

namespace App\Filament\Resources\Applicants\Schemas;

use App\Models\Applicant;
use App\Models\FinancialYear;
use App\Models\Institution;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApplicantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal Information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('other_name')
                            ->maxLength(255),
                        Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                            ])
                            ->required(),
                        DatePicker::make('date_of_birth')
                            ->required()
                            ->maxDate(now()->subYears(10)),
                        TextInput::make('national_id')
                            ->label('National ID / Birth Certificate')
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),
                    ]),

                Section::make('Contact Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('address')
                            ->columnSpanFull()
                            ->maxLength(255),
                    ]),

                Section::make('Application Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('application_number')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        TextInput::make('admission_number')
                            ->required(fn (Get $get): bool => ((float) ($get('amount_awarded') ?? 0)) > 0)
                            ->maxLength(50),
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
                        Select::make('institution_id')
                            ->label('Institution')
                            ->options(
                                fn () => Institution::query()
                                    ->when(
                                        filament()->getTenant(),
                                        fn ($q, $t) => $q->where('ward_id', $t->getKey())
                                    )
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('course')
                            ->maxLength(255),
                    ]),

                Section::make('Financial Assessment')
                    ->columns(3)
                    ->schema([
                        TextInput::make('need_assessment')
                            ->label('Need Assessment Score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0),
                        TextInput::make('fee_balance')
                            ->numeric()
                            ->prefix('KES')
                            ->default(0),
                        TextInput::make('amount_awarded')
                            ->numeric()
                            ->prefix('KES')
                            ->default(0),
                        DatePicker::make('awarded_at')
                            ->label('Award Date')
                            ->visible(fn (Get $get): bool => ((float) ($get('amount_awarded') ?? 0)) > 0),
                    ]),

                Section::make('Vulnerability / Category')
                    ->columns(3)
                    ->schema([
                        Select::make('orphan_status')
                            ->options([
                                Applicant::ORPHAN_STATUS_NONE    => 'Not an Orphan',
                                Applicant::ORPHAN_STATUS_PARTIAL => 'Partial Orphan (one parent deceased)',
                                Applicant::ORPHAN_STATUS_TOTAL   => 'Total Orphan (both parents deceased)',
                            ])
                            ->default(Applicant::ORPHAN_STATUS_NONE)
                            ->required(),
                        Toggle::make('has_disabled_parent')
                            ->label('Has a Disabled Parent'),
                        Toggle::make('has_disability')
                            ->label('Applicant has a Disability'),
                    ]),
            ]);
    }
}
