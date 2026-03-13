<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstitutionCheque extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CLEARED = 'cleared';
    public const STATUS_STALE = 'stale';
    public const STATUS_RETURNED = 'returned';

    protected $fillable = [
        'ward_id',
        'institution_id',
        'financial_year_id',
        'cheque_number',
        'cheque_date',
        'status',
        'cleared_at',
        'stale_at',
        'returned_at',
        'total_amount',
        'returned_amount',
        'remarks',
    ];

    protected $casts = [
        'cheque_date' => 'date',
        'cleared_at' => 'datetime',
        'stale_at' => 'datetime',
        'returned_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'returned_amount' => 'decimal:2',
    ];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function applicants(): BelongsToMany
    {
        return $this->belongsToMany(Applicant::class, 'applicant_institution_cheque')
            ->withTimestamps();
    }

    public function reconciliationItems(): HasMany
    {
        return $this->hasMany(BankReconciliationItem::class);
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CLEARED => 'Cleared',
            self::STATUS_STALE => 'Stale',
            self::STATUS_RETURNED => 'Returned to Unutilised',
        ];
    }

    public function getStaleDueDateAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->cheque_date?->copy()->addMonths(6);
    }

    public function isCleared(): bool
    {
        return $this->status === self::STATUS_CLEARED;
    }

    public function isReturned(): bool
    {
        return $this->status === self::STATUS_RETURNED;
    }

    public function isStaleEligible(): bool
    {
        return $this->status === self::STATUS_PENDING
            && blank($this->cleared_at)
            && blank($this->returned_at)
            && filled($this->stale_due_date)
            && now()->greaterThanOrEqualTo($this->stale_due_date);
    }

    public function canBeReturnedToUnutilised(): bool
    {
        return ! $this->isCleared()
            && ! $this->isReturned()
            && ($this->status === self::STATUS_STALE || $this->isStaleEligible());
    }
}
