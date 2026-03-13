<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankReconciliation extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_APPLIED = 'applied';

    protected $fillable = [
        'ward_id',
        'financial_year_id',
        'account_number',
        'account_name',
        'statement_period_start',
        'statement_period_end',
        'opening_balance',
        'closing_balance',
        'file_path',
        'status',
        'total_cheques_cleared',
        'total_cheques_bounced',
        'total_cleared_amount',
        'total_bounced_amount',
        'total_penalties',
        'total_bank_charges',
        'applied_at',
    ];

    protected $casts = [
        'statement_period_start' => 'date',
        'statement_period_end' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'total_cleared_amount' => 'decimal:2',
        'total_bounced_amount' => 'decimal:2',
        'total_penalties' => 'decimal:2',
        'total_bank_charges' => 'decimal:2',
        'applied_at' => 'datetime',
    ];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BankReconciliationItem::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isApplied(): bool
    {
        return $this->status === self::STATUS_APPLIED;
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_APPLIED => 'Applied',
        ];
    }
}
