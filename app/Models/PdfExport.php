<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdfExport extends Model
{
    protected $fillable = [
        'user_id',
        'institution_id',
        'storage_path',
        'status',
        'download_url',
        'filename',
        'expires_at',
        'error',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }
}
