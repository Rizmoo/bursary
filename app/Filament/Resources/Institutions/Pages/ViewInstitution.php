<?php

namespace App\Filament\Resources\Institutions\Pages;

use App\Filament\Resources\Institutions\InstitutionResource;
use App\Models\Applicant;
use App\Models\FinancialYear;
use App\Services\InstitutionChequeService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;

class ViewInstitution extends ViewRecord
{
    protected static string $resource = InstitutionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assignCheque')
                ->label('Assign Cheque')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->modalHeading('Assign cheque to institution')
                ->modalDescription('Create a cheque for this institution and select the awarded applicants who will benefit from it.')
                ->form([
                    Select::make('financial_year_id')
                        ->label('Financial Year')
                        ->options(fn () => FinancialYear::query()
                            ->where('ward_id', $this->getRecord()->ward_id)
                            ->orderByDesc('start_date')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),
                    TextInput::make('cheque_number')
                        ->label('Cheque Number')
                        ->required()
                        ->maxLength(255),
                    DatePicker::make('cheque_date')
                        ->label('Cheque Date')
                        ->default(now())
                        ->required(),
                    Select::make('applicant_ids')
                        ->label('Beneficiaries')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options(function (Get $get): array {
                            $financialYearId = $get('financial_year_id');

                            if (blank($financialYearId)) {
                                return [];
                            }

                            return Applicant::query()
                                ->whereBelongsTo($this->getRecord())
                                ->where('financial_year_id', $financialYearId)
                                ->beneficiaries()
                                ->whereDoesntHave('institutionCheques')
                                ->orderBy('last_name')
                                ->orderBy('first_name')
                                ->get()
                                ->mapWithKeys(fn (Applicant $applicant): array => [
                                    $applicant->id => trim(collect([$applicant->first_name, $applicant->other_name, $applicant->last_name])->filter()->implode(' ')) . ' - KES ' . number_format((float) $applicant->amount_awarded, 2),
                                ])
                                ->all();
                        })
                        ->helperText('Only applicants with an awarded amount above KES 0 and no existing cheque assignment are listed.')
                        ->required(),
                    Textarea::make('remarks')
                        ->label('Remarks')
                        ->rows(3),
                ])
                ->action(function (array $data, InstitutionChequeService $service) {
                    $cheque = $service->createForInstitution(
                        institution: $this->getRecord(),
                        financialYearId: (int) $data['financial_year_id'],
                        applicantIds: array_map('intval', $data['applicant_ids']),
                        attributes: $data,
                    );

                    return redirect()->route('institution-cheques.pdf', [
                        'tenant' => filament()->getTenant(),
                        'institution_cheque' => $cheque,
                    ]);
                }),
            EditAction::make(),
        ];
    }
}
