<?php

namespace App\Filament\Resources\Institutions;

use App\Filament\Resources\Institutions\Pages\CreateInstitution;
use App\Filament\Resources\Institutions\Pages\EditInstitution;
use App\Filament\Resources\Institutions\Pages\ListInstitutions;
use App\Filament\Resources\Institutions\Pages\ViewInstitution;
use App\Filament\Resources\Institutions\RelationManagers\ApplicantsRelationManager;
use App\Filament\Resources\Institutions\RelationManagers\InstitutionChequesRelationManager;
use App\Filament\Resources\Institutions\Schemas\InstitutionForm;
use App\Filament\Resources\Institutions\Schemas\InstitutionInfolist;
use App\Filament\Resources\Institutions\Tables\InstitutionsTable;
use App\Models\Institution;
use App\Support\FinancialYearScope;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InstitutionResource extends Resource
{
    protected static ?string $model = Institution::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string | UnitEnum | null $navigationGroup = 'Institutions';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return InstitutionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InstitutionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstitutionsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $financialYearId = optional(FinancialYearScope::resolveForTenant(filament()->getTenant()?->getKey()))->getKey();

        if (! $financialYearId) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($financialYearId): void {
            $builder
                ->whereHas('applicants', fn (Builder $q) => $q->where('financial_year_id', $financialYearId))
                ->orWhereHas('institutionCheques', fn (Builder $q) => $q->where('financial_year_id', $financialYearId));
        });
    }

    public static function getRelations(): array
    {
        return [
            ApplicantsRelationManager::class,
            InstitutionChequesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstitutions::route('/'),
            'create' => CreateInstitution::route('/create'),
            'view' => ViewInstitution::route('/{record}'),
            'edit' => EditInstitution::route('/{record}/edit'),
        ];
    }
}
