<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankReconciliationItem extends Model
{
    public const TYPE_BALANCE_BFWD = 'balance_bfwd';
    public const TYPE_BALANCE_END = 'balance_end';
    public const TYPE_CHEQUE_CLEARED = 'cheque_cleared';
    public const TYPE_CHEQUE_BOUNCED = 'cheque_bounced';
    public const TYPE_BOUNCED_REVERSAL = 'bounced_reversal';
    public const TYPE_PENALTY = 'penalty';
    public const TYPE_BANK_CHARGE = 'bank_charge';
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'bank_reconciliation_id',
        'transaction_date',
        'value_date',
        'description',
        'money_out',
        'money_in',
        'ledger_balance',
        'type',
        'cheque_number',
        'institution_cheque_id',
        'is_matched',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'value_date' => 'date',
        'money_out' => 'decimal:2',
        'money_in' => 'decimal:2',
        'ledger_balance' => 'decimal:2',
        'is_matched' => 'boolean',
    ];

    public function bankReconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class);
    }

    public function institutionCheque(): BelongsTo
    {
        return $this->belongsTo(InstitutionCheque::class);
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_BALANCE_BFWD => 'Balance B/FWD',
            self::TYPE_BALANCE_END => 'Balance at Period End',
            self::TYPE_CHEQUE_CLEARED => 'Cheque Cleared',
            self::TYPE_CHEQUE_BOUNCED => 'Cheque Bounced',
            self::TYPE_BOUNCED_REVERSAL => 'Bounced Reversal',
            self::TYPE_PENALTY => 'Penalty / Charge',
            self::TYPE_BANK_CHARGE => 'Bank Charge',
            self::TYPE_DEPOSIT => 'Deposit',
            self::TYPE_OTHER => 'Other',
        ];
    }

    public function getTypeLabel(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    public function getTypeColor(): string
    {
        return match ($this->type) {
            self::TYPE_CHEQUE_CLEARED => 'success',
            self::TYPE_CHEQUE_BOUNCED, self::TYPE_BOUNCED_REVERSAL => 'danger',
            self::TYPE_PENALTY, self::TYPE_BANK_CHARGE => 'warning',
            self::TYPE_DEPOSIT => 'info',
            default => 'gray',
        };
    }
}
