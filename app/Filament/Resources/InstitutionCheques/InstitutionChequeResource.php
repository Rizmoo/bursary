<?php

namespace App\Filament\Resources\InstitutionCheques;

use App\Filament\Resources\InstitutionCheques\Pages\EditInstitutionCheque;
use App\Filament\Resources\InstitutionCheques\Pages\ListInstitutionCheques;
use App\Filament\Resources\InstitutionCheques\Pages\ViewInstitutionCheque;
use App\Filament\Resources\InstitutionCheques\Schemas\InstitutionChequeForm;
use App\Filament\Resources\InstitutionCheques\Schemas\InstitutionChequeInfolist;
use App\Filament\Resources\InstitutionCheques\Tables\InstitutionChequesTable;
use App\Models\InstitutionCheque;
use App\Support\FinancialYearScope;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class InstitutionChequeResource extends Resource
{
    protected static ?string $model = InstitutionCheque::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static string | UnitEnum | null $navigationGroup = 'Finance';

    protected static ?string $recordTitleAttribute = 'cheque_number';

    public static function form(Schema $schema): Schema
    {
        return InstitutionChequeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InstitutionChequeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstitutionChequesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $financialYearId = optional(FinancialYearScope::resolveForTenant(filament()->getTenant()?->getKey()))->getKey();

        return $query->when($financialYearId, fn (Builder $builder) => $builder->where('financial_year_id', $financialYearId));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstitutionCheques::route('/'),
            'view' => ViewInstitutionCheque::route('/{record}'),
            'edit' => EditInstitutionCheque::route('/{record}/edit'),
        ];
    }
}
