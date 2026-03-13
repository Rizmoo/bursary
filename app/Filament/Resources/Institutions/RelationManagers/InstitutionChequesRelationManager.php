<?php

namespace App\Filament\Resources\Institutions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InstitutionChequesRelationManager extends RelationManager
{
    protected static string $relationship = 'institutionCheques';

    protected static ?string $title = 'Cheques';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('financialYear'))
            ->columns([
                TextColumn::make('cheque_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('financialYear.name')
                    ->label('Financial Year')
                    ->sortable(),
                TextColumn::make('cheque_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('applicants_count')
                    ->label('Beneficiaries')
                    ->counts('applicants')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->money('KES')
                    ->sortable(),
                TextColumn::make('remarks')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('financial_year_id')
                    ->label('Financial Year')
                    ->options(fn () => $this->getOwnerRecord()->ward->financialYears()->orderByDesc('start_date')->pluck('name', 'id')),
            ]);
    }
}
