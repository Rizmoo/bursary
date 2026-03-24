<?php

namespace App\Filament\Resources\Counties;

use App\Filament\Resources\Counties\Pages\CreateCounty;
use App\Filament\Resources\Counties\Pages\EditCounty;
use App\Filament\Resources\Counties\Pages\ListCounties;
use App\Models\County;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CountyResource extends Resource
{
    protected static ?string $model = County::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->is_admin ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('county_admin_name')
                ->required()
                ->dehydrated(false)
                ->label('County admin name')
                ->maxLength(255),
            TextInput::make('county_admin_email')
                ->required()
                ->email()
                ->dehydrated(false)
                ->label('County admin email')
                ->maxLength(255),
            TextInput::make('county_admin_password')
                ->required()
                ->password()
                ->dehydrated(false)
                ->label('County admin password')
                ->minLength(8),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('wards_count')->counts('wards')->label('Wards'),
            TextColumn::make('users_count')->counts('users')->label('Users'),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCounties::route('/'),
            'create' => CreateCounty::route('/create'),
            'edit' => EditCounty::route('/{record}/edit'),
        ];
    }
}
