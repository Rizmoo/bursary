<?php

namespace App\Filament\Resources\InstitutionCheques\Tables;

use App\Filament\Resources\InstitutionCheques\Actions\DownloadInstitutionChequePdfAction;
use App\Filament\Resources\InstitutionCheques\Actions\DownloadInstitutionChequeExcelAction;
use App\Filament\Resources\InstitutionCheques\Actions\InstitutionChequeLifecycleActions;
use App\Models\InstitutionCheque;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\BulkActionGroup;
use App\Filament\Resources\InstitutionCheques\Actions\DownloadAllInstitutionChequesPdfAction;

class InstitutionChequesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cheque_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('institution.name')
                    ->label('Institution')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('financialYear.name')
                    ->label('Financial Year')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => InstitutionCheque::getStatuses()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        InstitutionCheque::STATUS_CLEARED => 'success',
                        InstitutionCheque::STATUS_STALE => 'warning',
                        InstitutionCheque::STATUS_RETURNED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('cheque_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('stale_due_date')
                    ->label('Stale On')
                    ->date()
                    ->state(fn (InstitutionCheque $record) => $record->stale_due_date)
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('cheque_date', $direction)),
                TextColumn::make('applicants_count')
                    ->label('Beneficiaries')
                    ->counts('applicants')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->money('KES')
                    ->sortable(),
                TextColumn::make('returned_amount')
                    ->money('KES')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(InstitutionCheque::getStatuses()),
                SelectFilter::make('financial_year_id')
                    ->label('Financial Year')
                    ->relationship('financialYear', 'name'),
                Filter::make('stale_eligible')
                    ->label('Stale Eligible')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('status', InstitutionCheque::STATUS_PENDING)
                        ->whereDate('cheque_date', '<=', now()->subMonths(6)->toDateString())),
            ])
            ->defaultSort('cheque_date', 'desc')
            ->recordActions([
                DownloadInstitutionChequeExcelAction::make(),
                DownloadInstitutionChequePdfAction::make(),
                InstitutionChequeLifecycleActions::markCleared(),
                InstitutionChequeLifecycleActions::markStale(),
                InstitutionChequeLifecycleActions::returnToUnutilised(),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->actions([
                DownloadAllInstitutionChequesPdfAction::make(),
            ]);
    }
}
