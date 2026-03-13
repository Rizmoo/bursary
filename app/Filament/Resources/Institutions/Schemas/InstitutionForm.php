<?php

namespace App\Filament\Resources\Institutions\Schemas;

use App\Models\InstitutionCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InstitutionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Select::make('category_id')
                            ->label('Category')
                            ->options(
                                fn () => InstitutionCategory::query()
                                    ->when(
                                        filament()->getTenant(),
                                        fn ($q, $t) => $q->where('ward_id', $t->getKey())
                                    )
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('address')
                            ->maxLength(255),
                    ]),

                Section::make('Contact Details')
                    ->columns(3)
                    ->schema([
                        TextInput::make('contact_person')
                            ->maxLength(255),
                        TextInput::make('contact_email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('contact_phone')
                            ->tel()
                            ->maxLength(20),
                    ]),
            ]);
    }
}
