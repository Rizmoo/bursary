<?php

namespace App\Filament\Resources\InstitutionCheques\Actions;

use App\Models\InstitutionCheque;
use App\Services\InstitutionChequeLifecycleService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class InstitutionChequeLifecycleActions
{
    public static function markCleared(): Action
    {
        return Action::make('markCleared')
            ->label('Mark Cleared')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (InstitutionCheque $record): bool => ! $record->isCleared() && ! $record->isReturned())
            ->action(function (InstitutionCheque $record, InstitutionChequeLifecycleService $service): void {
                $service->markAsCleared($record);

                Notification::make()
                    ->title('Cheque marked as cleared')
                    ->success()
                    ->send();
            });
    }

    public static function markStale(): Action
    {
        return Action::make('markStale')
            ->label('Mark Stale')
            ->icon('heroicon-o-clock')
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (InstitutionCheque $record): bool => $record->isStaleEligible())
            ->action(function (InstitutionCheque $record, InstitutionChequeLifecycleService $service): void {
                $service->markAsStale($record);

                Notification::make()
                    ->title('Cheque marked as stale')
                    ->success()
                    ->send();
            });
    }

    public static function returnToUnutilised(): Action
    {
        return Action::make('returnToUnutilised')
            ->label('Return to Unutilised')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription('This will mark the cheque as returned and add the cheque amount back to the financial year unutilised amount.')
            ->visible(fn (InstitutionCheque $record): bool => $record->canBeReturnedToUnutilised())
            ->action(function (InstitutionCheque $record, InstitutionChequeLifecycleService $service): void {
                $service->returnToUnutilised($record);

                Notification::make()
                    ->title('Amount returned to unutilised')
                    ->success()
                    ->send();
            });
    }
}
