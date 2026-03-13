<?php

namespace App\Services;

use App\Models\InstitutionCheque;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstitutionChequeLifecycleService
{
    public function markAsCleared(InstitutionCheque $cheque): InstitutionCheque
    {
        if ($cheque->isReturned()) {
            throw ValidationException::withMessages([
                'status' => 'A returned cheque cannot be marked as cleared.',
            ]);
        }

        return DB::transaction(function () use ($cheque): InstitutionCheque {
            $cheque->forceFill([
                'status' => InstitutionCheque::STATUS_CLEARED,
                'cleared_at' => now(),
                'stale_at' => null,
            ])->save();

            return $cheque->refresh();
        });
    }

    public function markAsStale(InstitutionCheque $cheque): InstitutionCheque
    {
        if (! $cheque->isStaleEligible()) {
            throw ValidationException::withMessages([
                'status' => 'Only uncleared cheques older than 6 months can be marked as stale.',
            ]);
        }

        return DB::transaction(function () use ($cheque): InstitutionCheque {
            $cheque->forceFill([
                'status' => InstitutionCheque::STATUS_STALE,
                'stale_at' => now(),
            ])->save();

            return $cheque->refresh();
        });
    }

    public function returnToUnutilised(InstitutionCheque $cheque): InstitutionCheque
    {
        if (! $cheque->canBeReturnedToUnutilised()) {
            throw ValidationException::withMessages([
                'status' => 'Only stale or stale-eligible cheques can be returned to unutilised.',
            ]);
        }

        if ($cheque->isReturned()) {
            throw ValidationException::withMessages([
                'status' => 'This cheque has already been returned to unutilised.',
            ]);
        }

        return DB::transaction(function () use ($cheque): InstitutionCheque {
            $amount = (float) $cheque->total_amount;

            $cheque->financialYear()->increment('unutilised_amount', $amount);

            $cheque->forceFill([
                'status' => InstitutionCheque::STATUS_RETURNED,
                'stale_at' => $cheque->stale_at ?? now(),
                'returned_at' => now(),
                'returned_amount' => $amount,
            ])->save();

            return $cheque->refresh();
        });
    }
}
