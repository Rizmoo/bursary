<?php

namespace App\Services;

use App\Models\BankReconciliation;
use App\Models\BankReconciliationItem;
use App\Models\InstitutionCheque;
use App\Models\Ward;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BankReconciliationService
{
    public function __construct(
        protected BankStatementParserService $parser,
        protected InstitutionChequeLifecycleService $lifecycleService,
    ) {}

    /**
     * Upload and parse a bank statement PDF, creating a draft reconciliation.
     */
    public function createFromUpload(Ward $ward, string $filePath, ?int $financialYearId = null): BankReconciliation
    {
        $absolutePath = Storage::disk('local')->path($filePath);
        $parsed = $this->parser->parse($absolutePath);

        return DB::transaction(function () use ($ward, $filePath, $financialYearId, $parsed): BankReconciliation {
            $reconciliation = BankReconciliation::create([
                'ward_id' => $ward->id,
                'financial_year_id' => $financialYearId,
                'account_number' => $parsed['account_number'],
                'account_name' => $parsed['account_name'],
                'statement_period_start' => $parsed['period_start'],
                'statement_period_end' => $parsed['period_end'],
                'opening_balance' => $parsed['opening_balance'],
                'closing_balance' => $parsed['closing_balance'],
                'file_path' => $filePath,
                'status' => BankReconciliation::STATUS_DRAFT,
            ]);

            // Create items and match cheques
            $totalClearedAmount = 0.0;
            $totalBouncedAmount = 0.0;
            $totalPenalties = 0.0;
            $totalBankCharges = 0.0;
            $clearedCount = 0;
            $bouncedCount = 0;

            foreach ($parsed['transactions'] as $txn) {
                $institutionChequeId = null;
                $isMatched = false;

                // Try to match cheque number to system cheques
                if (filled($txn['cheque_number'])) {
                    $systemCheque = $this->findSystemCheque($ward, $txn['cheque_number']);
                    if ($systemCheque) {
                        $institutionChequeId = $systemCheque->id;
                        $isMatched = true;
                    }
                }

                $item = $reconciliation->items()->create([
                    'transaction_date' => $txn['date'],
                    'value_date' => $txn['value_date'],
                    'description' => $txn['description'],
                    'money_out' => abs($txn['money_out']),
                    'money_in' => abs($txn['money_in']),
                    'ledger_balance' => $txn['ledger_balance'],
                    'type' => $txn['type'],
                    'cheque_number' => $txn['cheque_number'],
                    'institution_cheque_id' => $institutionChequeId,
                    'is_matched' => $isMatched,
                ]);

                // Accumulate totals
                match ($txn['type']) {
                    BankReconciliationItem::TYPE_CHEQUE_CLEARED => (function () use (&$totalClearedAmount, &$clearedCount, $txn) {
                        $totalClearedAmount += abs($txn['money_out']);
                        $clearedCount++;
                    })(),
                    BankReconciliationItem::TYPE_CHEQUE_BOUNCED,
                    BankReconciliationItem::TYPE_BOUNCED_REVERSAL => (function () use (&$totalBouncedAmount, &$bouncedCount, $txn) {
                        $totalBouncedAmount += abs($txn['money_in'] ?: $txn['money_out']);
                        $bouncedCount++;
                    })(),
                    BankReconciliationItem::TYPE_PENALTY => (function () use (&$totalPenalties, $txn) {
                        $totalPenalties += abs($txn['money_out']);
                    })(),
                    BankReconciliationItem::TYPE_BANK_CHARGE => (function () use (&$totalBankCharges, $txn) {
                        $totalBankCharges += abs($txn['money_out']);
                    })(),
                    default => null,
                };
            }

            $reconciliation->update([
                'total_cheques_cleared' => $clearedCount,
                'total_cheques_bounced' => $bouncedCount,
                'total_cleared_amount' => $totalClearedAmount,
                'total_bounced_amount' => $totalBouncedAmount,
                'total_penalties' => $totalPenalties,
                'total_bank_charges' => $totalBankCharges,
            ]);

            return $reconciliation->load('items');
        });
    }

    /**
     * Apply the reconciliation: update system cheques based on matched items.
     */
    public function apply(BankReconciliation $reconciliation): BankReconciliation
    {
        if ($reconciliation->isApplied()) {
            throw ValidationException::withMessages([
                'status' => 'This reconciliation has already been applied.',
            ]);
        }

        return DB::transaction(function () use ($reconciliation): BankReconciliation {
            $matchedItems = $reconciliation->items()
                ->where('is_matched', true)
                ->whereNotNull('institution_cheque_id')
                ->get();

            // Track which cheques have been bounced so we can handle the pair
            $bouncedChequeNumbers = $reconciliation->items()
                ->whereIn('type', [BankReconciliationItem::TYPE_BOUNCED_REVERSAL, BankReconciliationItem::TYPE_CHEQUE_BOUNCED])
                ->where('is_matched', true)
                ->pluck('cheque_number')
                ->unique()
                ->filter()
                ->toArray();

            foreach ($matchedItems as $item) {
                $cheque = InstitutionCheque::find($item->institution_cheque_id);
                if (! $cheque) {
                    continue;
                }

                // If this cheque number appears in bounced items, mark as returned
                if (in_array($item->cheque_number, $bouncedChequeNumbers)) {
                    if ($cheque->status === InstitutionCheque::STATUS_PENDING) {
                        // Bounced cheque: mark stale then return to unutilised
                        $cheque->forceFill([
                            'status' => InstitutionCheque::STATUS_STALE,
                            'stale_at' => now(),
                        ])->save();

                        $this->lifecycleService->returnToUnutilised($cheque->refresh());
                    }

                    continue;
                }

                // Cleared cheque
                if ($item->type === BankReconciliationItem::TYPE_CHEQUE_CLEARED
                    && $cheque->status === InstitutionCheque::STATUS_PENDING) {
                    $this->lifecycleService->markAsCleared($cheque);
                }
            }

            $reconciliation->forceFill([
                'status' => BankReconciliation::STATUS_APPLIED,
                'applied_at' => now(),
            ])->save();

            return $reconciliation->refresh()->load('items');
        });
    }

    /**
     * Find a system cheque by its number (with fuzzy matching on leading zeros).
     */
    protected function findSystemCheque(Ward $ward, string $chequeNumber): ?InstitutionCheque
    {
        // Try exact match first
        $cheque = InstitutionCheque::query()
            ->where('ward_id', $ward->id)
            ->where('cheque_number', $chequeNumber)
            ->first();

        if ($cheque) {
            return $cheque;
        }

        // Try without leading zeros
        $normalized = ltrim($chequeNumber, '0');
        if ($normalized !== $chequeNumber) {
            $cheque = InstitutionCheque::query()
                ->where('ward_id', $ward->id)
                ->where('cheque_number', $normalized)
                ->first();
            if ($cheque) {
                return $cheque;
            }
        }

        // Try LIKE match (cheque number might be stored with different padding)
        return InstitutionCheque::query()
            ->where('ward_id', $ward->id)
            ->where('cheque_number', 'LIKE', '%'.$normalized)
            ->first();
    }

    /**
     * Get reconciliation summary data for display.
     */
    public function getSummary(BankReconciliation $reconciliation): array
    {
        $items = $reconciliation->items()->with('institutionCheque.institution')->get();

        $clearedItems = $items->where('type', BankReconciliationItem::TYPE_CHEQUE_CLEARED);
        $bouncedItems = $items->whereIn('type', [
            BankReconciliationItem::TYPE_BOUNCED_REVERSAL,
            BankReconciliationItem::TYPE_CHEQUE_BOUNCED,
        ]);
        $penaltyItems = $items->where('type', BankReconciliationItem::TYPE_PENALTY);
        $bankChargeItems = $items->where('type', BankReconciliationItem::TYPE_BANK_CHARGE);
        $depositItems = $items->where('type', BankReconciliationItem::TYPE_DEPOSIT);
        $otherItems = $items->whereIn('type', [BankReconciliationItem::TYPE_OTHER]);

        $matchedCount = $items->where('is_matched', true)->count();
        $unmatchedCheques = $items
            ->whereIn('type', [
                BankReconciliationItem::TYPE_CHEQUE_CLEARED,
                BankReconciliationItem::TYPE_BOUNCED_REVERSAL,
                BankReconciliationItem::TYPE_CHEQUE_BOUNCED,
            ])
            ->where('is_matched', false);

        return [
            'items' => $items,
            'cleared' => $clearedItems,
            'bounced' => $bouncedItems,
            'penalties' => $penaltyItems,
            'bank_charges' => $bankChargeItems,
            'deposits' => $depositItems,
            'other' => $otherItems,
            'matched_count' => $matchedCount,
            'unmatched_cheques' => $unmatchedCheques,
        ];
    }
}
