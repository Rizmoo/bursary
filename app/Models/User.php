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
        'is_admin',
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

                return;
            }

            if (blank($user->ward_id) && ! app()->runningInConsole()) {
                throw ValidationException::withMessages([
                    'ward_id' => 'A ward is required for non-admin users.',
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
            'password' => 'hashed',
        ];
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if (! $tenant instanceof Ward) {
            return false;
        }

        if ($this->is_admin) {
            return true;
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

        return $this->ward ? [$this->ward] : [];
    }
}
