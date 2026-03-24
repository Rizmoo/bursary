<?php

namespace App\Filament\App\Resources\Wards;

use App\Filament\App\Resources\Wards\Pages\CreateWard;
use App\Filament\App\Resources\Wards\Pages\EditWard;
use App\Filament\App\Resources\Wards\Pages\ListWards;
use App\Models\Ward;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class WardResource extends Resource
{
    protected static ?string $model = Ward::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $modelLabel = 'Ward';

    protected static ?string $pluralModelLabel = 'Wards';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('ward_user_name')
                ->required()
                ->dehydrated(false)
                ->maxLength(255),
            TextInput::make('ward_user_email')
                ->required()
                ->email()
                ->dehydrated(false)
                ->maxLength(255),
            TextInput::make('ward_user_password')
                ->password()
                ->dehydrated(false)
                ->minLength(8)
                ->required(fn (string $operation): bool => $operation === 'create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('users.name')
                ->label('Assigned user')
                ->badge()
                ->limit(1),
            TextColumn::make('users.email')
                ->label('User email')
                ->limit(1),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user?->is_admin) {
            return $query;
        }

        return $query->where('county_id', $user?->county_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWards::route('/'),
            'create' => CreateWard::route('/create'),
            'edit' => EditWard::route('/{record}/edit'),
        ];
    }
}
