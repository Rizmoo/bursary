<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = [
        'county_id',
        'name',
    ];

    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function financialYears(): HasMany
    {
        return $this->hasMany(FinancialYear::class);
    }

    public function institutionCategories(): HasMany
    {
        return $this->hasMany(InstitutionCategory::class);
    }

    public function institutions(): HasMany
    {
        return $this->hasMany(Institution::class);
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
