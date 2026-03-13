<?php

namespace App\Filament\Resources\InstitutionCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InstitutionCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
