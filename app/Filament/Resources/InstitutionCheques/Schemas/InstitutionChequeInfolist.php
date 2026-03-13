<?php

namespace App\Filament\Resources\InstitutionCheques\Schemas;

use App\Models\InstitutionCheque;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InstitutionChequeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cheque Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('cheque_number'),
                        TextEntry::make('cheque_date')->date(),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => InstitutionCheque::getStatuses()[$state] ?? $state)
                            ->color(fn (string $state): string => match ($state) {
                                InstitutionCheque::STATUS_CLEARED => 'success',
                                InstitutionCheque::STATUS_STALE => 'warning',
                                InstitutionCheque::STATUS_RETURNED => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('institution.name')->label('Institution'),
                        TextEntry::make('financialYear.name')->label('Financial Year'),
                        TextEntry::make('applicants_count')
                            ->label('Beneficiaries')
                            ->state(fn (InstitutionCheque $record): int => $record->applicants()->count()),
                    ]),
                Section::make('Lifecycle')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('cleared_at')->dateTime(),
                        TextEntry::make('stale_at')->dateTime(),
                        TextEntry::make('returned_at')->dateTime(),
                        TextEntry::make('stale_due_date')->date(),
                    ]),
                Section::make('Financials')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('total_amount')->money('KES'),
                        TextEntry::make('returned_amount')->money('KES'),
                        TextEntry::make('financialYear.unutilised_amount')
                            ->label('FY Unutilised Amount')
                            ->money('KES'),
                    ]),
                Section::make('Beneficiaries')
                    ->schema([
                        TextEntry::make('beneficiaries_list')
                            ->label('Applicants')
                            ->state(fn (InstitutionCheque $record): string => $record->applicants()
                                ->orderBy('last_name')
                                ->orderBy('first_name')
                                ->get()
                                ->map(fn ($applicant): string => trim(collect([$applicant->first_name, $applicant->other_name, $applicant->last_name])->filter()->implode(' ')) . ' - KES ' . number_format((float) $applicant->amount_awarded, 2))
                                ->implode("\n"))
                            ->columnSpanFull(),
                        TextEntry::make('remarks')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
