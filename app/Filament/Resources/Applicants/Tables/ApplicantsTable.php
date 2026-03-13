<?php

namespace App\Filament\Resources\Applicants\Tables;

use App\Models\Applicant;
use App\Models\Institution;
use App\Support\FinancialYearScope;
use App\Services\InstitutionChequeService;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ApplicantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('application_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('admission_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->badge()
                    ->sortable(),
                TextColumn::make('financialYear.name')
                    ->label('Financial Year')
                    ->sortable(),
                TextColumn::make('institution.name')
                    ->label('Institution')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('is_beneficiary')
                    ->label('Beneficiary')
                    ->boolean()
                    ->state(fn (Applicant $record): bool => $record->isBeneficiary()),
                TextColumn::make('orphan_status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'none'    => 'Not Orphan',
                        'partial' => 'Partial Orphan',
                        'total'   => 'Total Orphan',
                        default   => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'partial' => 'warning',
                        'total'   => 'danger',
                        default   => 'gray',
                    }),
                IconColumn::make('has_disabled_parent')
                    ->label('Disabled Parent')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('has_disability')
                    ->label('Disabled')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('amount_awarded')
                    ->money('KES')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('financial_year_id')
                    ->label('Financial Year')
                    ->relationship('financialYear', 'name'),
                SelectFilter::make('institution_id')
                    ->label('Institution')
                    ->options(function () {
                        $tenantId = filament()->getTenant()?->getKey();
                        $financialYearId = optional(FinancialYearScope::resolveForTenant($tenantId))->getKey();

                        return Institution::query()
                            ->when($tenantId, fn ($query) => $query->where('ward_id', $tenantId))
                            ->when($financialYearId, fn ($query) => $query->whereHas('applicants', fn ($q) => $q->where('financial_year_id', $financialYearId)))
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload(),
                SelectFilter::make('orphan_status')
                    ->options([
                        'none'    => 'Not Orphan',
                        'partial' => 'Partial Orphan',
                        'total'   => 'Total Orphan',
                    ]),
                TernaryFilter::make('has_disabled_parent')
                    ->label('Disabled Parent'),
                TernaryFilter::make('has_disability')
                    ->label('Applicant Disabled'),
                TernaryFilter::make('is_beneficiary')
                    ->label('Beneficiary')
                    ->queries(
                        true: fn (Builder $query) => $query->where('amount_awarded', '>', 0),
                        false: fn (Builder $query) => $query->where('amount_awarded', '<=', 0),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assign_cheque')
                        ->label('Assign Cheque')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->deselectRecordsAfterCompletion()
                        ->modalHeading('Assign cheque to selected beneficiaries')
                        ->modalDescription('Select awarded applicants from the same institution and financial year. A cheque will be created and linked to the selected beneficiaries.')
                        ->form([
                            TextInput::make('cheque_number')
                                ->label('Cheque Number')
                                ->required()
                                ->maxLength(255),
                            DatePicker::make('cheque_date')
                                ->label('Cheque Date')
                                ->default(now())
                                ->required(),
                            Textarea::make('remarks')
                                ->label('Remarks')
                                ->rows(3),
                        ])
                        ->action(function (Collection $records, array $data, InstitutionChequeService $service) {
                            $cheque = $service->createForApplicants($records, $data);

                            return redirect()->route('institution-cheques.pdf', [
                                'tenant' => filament()->getTenant(),
                                'institution_cheque' => $cheque,
                            ]);
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
