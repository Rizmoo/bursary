<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'ward_id',
        'name',
        'start_date',
        'end_date',
        'is_current',
        'opening_balance',
        'closing_balance',
        'unutilised_amount',
        'budget',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'unutilised_amount' => 'decimal:2',
        'budget' => 'decimal:2',
    ];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }

    public function institutionCheques(): HasMany
    {
        return $this->hasMany(InstitutionCheque::class);
    }

    public function bankReconciliations(): HasMany
    {
        return $this->hasMany(BankReconciliation::class);
    }
}
