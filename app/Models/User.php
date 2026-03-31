<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Notifications\VerifyEmailNotification;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, TwoFactorAuthenticatable, LogsActivity;

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            // /rbdashboard — only super_admin and admin
            return $this->hasAnyRole(['super_admin', 'admin']);
        }

        if ($panel->getId() === 'client-area') {
            // Admins can preview the client area without email verification
            if ($this->hasAnyRole(['super_admin', 'admin'])) {
                return true;
            }

            // Grant panel entry to any user with a client role, verified OR NOT.
            // Filament's emailVerification(isRequired: true) middleware handles the
            // redirect to /client-area/email-verification/prompt for unverified users.
            // Blocking here (with hasVerifiedEmail()) causes a 403 before the prompt
            // can ever be shown — that is the bug we are fixing.
            return $this->hasAnyRole(['premium', 'regular_user']);
        }

        return false;
    }

    /**
     * Send the email verification notification using our branded template.
     *
     * Sent synchronously (notify, not notifyNow) so it respects the user's
     * notification channels. The VerifyEmailNotification class itself does NOT
     * implement ShouldQueue, so it is always delivered inline regardless of
     * the QUEUE_CONNECTION setting — no queue worker needed for email verification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Returns true if this user is an admin/super_admin OR has verified their email.
     * Used so admin staff are never blocked by the email verification gate.
     */
    public function hasVerifiedEmailOrIsAdmin(): bool
    {
        if ($this->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return $this->hasVerifiedEmail();
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
