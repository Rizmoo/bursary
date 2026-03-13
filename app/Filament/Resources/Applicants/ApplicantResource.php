<?php

namespace App\Filament\Resources\Applicants;

use App\Filament\Resources\Applicants\Pages\CreateApplicant;
use App\Filament\Resources\Applicants\Pages\EditApplicant;
use App\Filament\Resources\Applicants\Pages\ListApplicants;
use App\Filament\Resources\Applicants\Pages\ViewApplicant;
use App\Filament\Resources\Applicants\Schemas\ApplicantForm;
use App\Filament\Resources\Applicants\Schemas\ApplicantInfolist;
use App\Filament\Resources\Applicants\Tables\ApplicantsTable;
use App\Models\Applicant;
use App\Support\FinancialYearScope;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ApplicantResource extends Resource
{
    protected static ?string $model = Applicant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string | UnitEnum | null $navigationGroup = 'Bursary';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ApplicantForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ApplicantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicantsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $financialYearId = optional(FinancialYearScope::resolveForTenant(filament()->getTenant()?->getKey()))->getKey();

        return $query->when($financialYearId, fn (Builder $builder) => $builder->where('financial_year_id', $financialYearId));
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
            'index' => ListApplicants::route('/'),
            'create' => CreateApplicant::route('/create'),
            'view' => ViewApplicant::route('/{record}'),
            'edit' => EditApplicant::route('/{record}/edit'),
        ];
    }
}
