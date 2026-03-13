<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Validation\ValidationException;

class Applicant extends Model
{
    public const ORPHAN_STATUS_NONE = 'none';
    public const ORPHAN_STATUS_PARTIAL = 'partial';
    public const ORPHAN_STATUS_TOTAL = 'total';

    protected $fillable = [
        'ward_id',
        'application_number',
        'admission_number',
        'first_name',
        'last_name',
        'other_name',
        'gender',
        'name',
        'email',
        'phone',
        'address',
        'national_id',
        'date_of_birth',
        'financial_year_id',
        'institution_id',
        'course',
        'amount_awarded',
        'awarded_at',
        'need_assessment',
        'fee_balance',
        'orphan_status',
        'has_disabled_parent',
        'has_disability',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'amount_awarded' => 'decimal:2',
        'awarded_at' => 'date',
        'fee_balance' => 'decimal:2',
        'has_disabled_parent' => 'boolean',
        'has_disability' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Applicant $applicant): void {
            if (((float) $applicant->amount_awarded) > 0) {
                if (blank(trim((string) $applicant->admission_number))) {
                    throw ValidationException::withMessages([
                        'admission_number' => 'Applicants without an admission number cannot be awarded.',
                    ]);
                }

                $applicant->awarded_at ??= now()->toDateString();

                return;
            }

            $applicant->awarded_at = null;
        });
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function institutionCheques(): BelongsToMany
    {
        return $this->belongsToMany(InstitutionCheque::class, 'applicant_institution_cheque')
            ->withTimestamps();
    }

    public function scopeBeneficiaries(Builder $query): Builder
    {
        return $query->where('amount_awarded', '>', 0);
    }

    public function isBeneficiary(): bool
    {
        return (float) $this->amount_awarded > 0;
    }

    public function hasAdmissionNumber(): bool
    {
        return filled(trim((string) $this->admission_number));
    }
}
