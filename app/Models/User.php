<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            // /rbdashboard — hanya super_admin dan admin
            return $this->hasAnyRole(['super_admin', 'admin']);
        }

        if ($panel->getId() === 'client-area') {
            // /client-area — semua role valid bisa akses
            return $this->hasAnyRole(['super_admin', 'admin', 'premium', 'regular_user']);
        }

        return false;
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Ambil profil yang ada atau buat profil baru jika belum ada.
     */
    public function getOrCreateProfile(): Profile
    {
        return $this->profile ?? $this->profile()->create([
            'custom_url_slug' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $this->name)) . '-' . rand(100, 999),
        ]);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
