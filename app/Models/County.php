<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class County extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function wards(): HasMany
    {
        return $this->hasMany(Ward::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
