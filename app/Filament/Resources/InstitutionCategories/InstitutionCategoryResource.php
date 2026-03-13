<?php

namespace App\Filament\Resources\InstitutionCategories;

use App\Filament\Resources\InstitutionCategories\Pages\CreateInstitutionCategory;
use App\Filament\Resources\InstitutionCategories\Pages\EditInstitutionCategory;
use App\Filament\Resources\InstitutionCategories\Pages\ListInstitutionCategories;
use App\Filament\Resources\InstitutionCategories\Pages\ViewInstitutionCategory;
use App\Filament\Resources\InstitutionCategories\Schemas\InstitutionCategoryForm;
use App\Filament\Resources\InstitutionCategories\Schemas\InstitutionCategoryInfolist;
use App\Filament\Resources\InstitutionCategories\Tables\InstitutionCategoriesTable;
use App\Models\InstitutionCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InstitutionCategoryResource extends Resource
{
    protected static ?string $model = InstitutionCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string | UnitEnum | null $navigationGroup = 'Institutions';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return InstitutionCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InstitutionCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstitutionCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstitutionCategories::route('/'),
            'create' => CreateInstitutionCategory::route('/create'),
            'view' => ViewInstitutionCategory::route('/{record}'),
            'edit' => EditInstitutionCategory::route('/{record}/edit'),
        ];
    }
}
