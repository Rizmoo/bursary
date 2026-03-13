<?php

namespace App\Filament\Resources\Institutions\RelationManagers;

use App\Services\InstitutionChequeService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class ApplicantsRelationManager extends RelationManager
{
    protected static string $relationship = 'applicants';

    protected static ?string $title = 'Applicants';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['financialYear', 'institutionCheques']))
            ->columns([
                TextColumn::make('application_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('financialYear.name')
                    ->label('Financial Year')
                    ->sortable(),
                TextColumn::make('gender')
                    ->badge(),
                IconColumn::make('is_beneficiary')
                    ->label('Beneficiary')
                    ->boolean()
                    ->state(fn ($record): bool => $record->isBeneficiary()),
                TextColumn::make('amount_awarded')
                    ->money('KES')
                    ->sortable(),
                TextColumn::make('cheque_numbers')
                    ->label('Cheque Number')
                    ->state(fn ($record): string => $record->institutionCheques->pluck('cheque_number')->implode(', '))
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('financial_year_id')
                    ->label('Financial Year')
                    ->options(fn () => $this->getOwnerRecord()->ward->financialYears()->orderByDesc('start_date')->pluck('name', 'id')),
                TernaryFilter::make('is_beneficiary')
                    ->label('Beneficiary')
                    ->queries(
                        true: fn (Builder $query) => $query->where('amount_awarded', '>', 0),
                        false: fn (Builder $query) => $query->where('amount_awarded', '<=', 0),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->headerActions([
                Action::make('assignCurrentFyCheque')
                    ->label('Assign Cheque (Current FY)')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->modalHeading('Assign cheque to current financial year beneficiaries')
                    ->modalDescription('This will assign one cheque to all eligible beneficiaries in the current financial year for this institution.')
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
                    ->action(function (array $data, InstitutionChequeService $service): void {
                        $institution = $this->getOwnerRecord();

                        $financialYear = $institution->ward->financialYears()
                            ->where('is_current', true)
                            ->latest('start_date')
                            ->first();

                        if (! $financialYear) {
                            throw ValidationException::withMessages([
                                'financial_year_id' => 'No current financial year is configured for this ward.',
                            ]);
                        }

                        $applicantIds = $institution->applicants()
                            ->where('financial_year_id', $financialYear->id)
                            ->where('amount_awarded', '>', 0)
                            ->whereNotNull('admission_number')
                            ->where('admission_number', '!=', '')
                            ->whereDoesntHave('institutionCheques')
                            ->pluck('id')
                            ->all();

                        if ($applicantIds === []) {
                            throw ValidationException::withMessages([
                                'records' => 'No eligible current financial year beneficiaries found for cheque assignment.',
                            ]);
                        }

                        $cheque = $service->createForInstitution(
                            institution: $institution,
                            financialYearId: $financialYear->id,
                            applicantIds: $applicantIds,
                            attributes: $data,
                        );

                        Notification::make()
                            ->title('Cheque assigned successfully')
                            ->body("Cheque {$cheque->cheque_number} assigned to {$cheque->applicants()->count()} current FY beneficiary(s).")
                            ->success()
                            ->send();

                        $this->redirect(route('institution-cheques.pdf', [
                            'tenant' => filament()->getTenant(),
                            'institution_cheque' => $cheque,
                        ]));
                    }),
            ]);
    }
}
