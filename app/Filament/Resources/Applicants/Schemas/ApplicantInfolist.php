<?php

namespace App\Filament\Resources\Applicants\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApplicantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal Information')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('first_name'),
                        TextEntry::make('last_name'),
                        TextEntry::make('other_name'),
                        TextEntry::make('gender')->badge(),
                        TextEntry::make('date_of_birth')->date(),
                        TextEntry::make('national_id')->label('National ID / Birth Cert'),
                    ]),

                Section::make('Contact Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('phone'),
                        TextEntry::make('email'),
                        TextEntry::make('address')->columnSpanFull(),
                    ]),

                Section::make('Application Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('application_number'),
                        TextEntry::make('admission_number'),
                        TextEntry::make('financialYear.name')->label('Financial Year'),
                        TextEntry::make('institution.name')->label('Institution'),
                        TextEntry::make('course'),
                    ]),

                Section::make('Financial Assessment')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('need_assessment')->label('Need Assessment Score'),
                        TextEntry::make('fee_balance')->money('KES'),
                        TextEntry::make('amount_awarded')->money('KES'),
                        TextEntry::make('awarded_at')->date(),
                    ]),

                Section::make('Vulnerability / Category')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('orphan_status')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'none'    => 'Not an Orphan',
                                'partial' => 'Partial Orphan',
                                'total'   => 'Total Orphan',
                                default   => $state,
                            })
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'partial' => 'warning',
                                'total'   => 'danger',
                                default   => 'gray',
                            }),
                        IconEntry::make('has_disabled_parent')
                            ->label('Disabled Parent')
                            ->boolean(),
                        IconEntry::make('has_disability')
                            ->label('Applicant Disabled')
                            ->boolean(),
                    ]),
            ]);
    }
}
