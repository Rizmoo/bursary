<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;

class User extends Authenticatable implements HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ward_id',
        'county_id',
        'is_admin',
        'is_county_admin',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->is_admin) {
                $user->ward_id = null;
                $user->county_id = null;

                return;
            }

            if ($user->is_county_admin) {
                $user->ward_id = null;

                if (blank($user->county_id)) {
                    throw ValidationException::withMessages([
                        'county_id' => 'A county is required for county admin users.',
                    ]);
                }

                return;
            }

            if (blank($user->ward_id)) {
                throw ValidationException::withMessages([
                    'ward_id' => 'A ward is required for non-admin users.',
                ]);
            }

            if (blank($user->county_id)) {
                throw ValidationException::withMessages([
                    'county_id' => 'A county is required for ward users.',
                ]);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'is_county_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->is_admin || ! $this->is_county_admin;
        }

        if ($panel->getId() === 'app') {
            return $this->is_admin || $this->is_county_admin;
        }

        return false;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if (! $tenant instanceof Ward) {
            return false;
        }

        if ($this->is_admin) {
            return true;
        }

        if ($this->is_county_admin) {
            return $tenant->county_id === $this->county_id;
        }

        return $this->ward?->is($tenant) ?? false;
    }

    public function getTenants(Panel $panel): array | Collection
    {
        if ($this->is_admin) {
            return Ward::query()
                ->orderBy('name')
                ->get();
        }

        if ($this->is_county_admin) {
            return Ward::query()
                ->where('county_id', $this->county_id)
                ->orderBy('name')
                ->get();
        }

        return $this->ward ? [$this->ward] : [];
    }
}
