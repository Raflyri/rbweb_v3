<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthenticationLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeLogins($query)
    {
        return $query->where('event', 'login');
    }

    public function scopeLogouts($query)
    {
        return $query->where('event', 'logout');
    }

    public function scopeRecent($query, int $days = 60)
    {
        return $query->where('logged_at', '>=', now()->subDays($days));
    }
}
