<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    protected $fillable = [
        'ward_id',
        'name',
        'code',
        'address',
        'contact_person',
        'contact_email',
        'contact_phone',
        'category_id',
    ];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InstitutionCategory::class, 'category_id');
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }

    public function institutionCheques(): HasMany
    {
        return $this->hasMany(InstitutionCheque::class);
    }
}
