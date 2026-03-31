<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Livewire component that powers the "Resend Verification Email" button
 * shown in the email verification warning banner inside the Client Area.
 *
 * Features:
 *  - 60-second session-based cooldown to prevent spam
 *  - Success / error flash state for user feedback
 *  - Hidden entirely when the user is already verified
 */
class ResendVerificationEmail extends Component
{
    /** Seconds remaining in the cooldown window (0 = button enabled). */
    public int $cooldownSeconds = 0;

    /** Whether the last send was successful. */
    public bool $sent = false;

    /** Error message from the last send attempt. */
    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->refreshCooldown();
    }

    /**
     * Re-send the verification email when the button is clicked.
     */
    public function resend(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return;
        }

        if ($user->hasVerifiedEmail()) {
            // Already verified — nothing to do; page will hide the banner on next render
            return;
        }

        // Enforce cooldown
        $cooldownKey = 'verify_resend_until_' . $user->id;
        $cooldownUntil = session($cooldownKey);

        if ($cooldownUntil && now()->lt($cooldownUntil)) {
            $this->cooldownSeconds = (int) now()->diffInSeconds($cooldownUntil);
            return;
        }

        try {
            $user->sendEmailVerificationNotification();

            // Set 60-second cooldown
            session([$cooldownKey => now()->addSeconds(60)]);
            $this->cooldownSeconds = 60;
            $this->sent = true;
            $this->errorMessage = null;
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal mengirim email. Silakan coba lagi beberapa saat.';
            $this->sent = false;
            report($e);
        }
    }

    /**
     * Called by a browser polling interval (every second) to count down the cooldown.
     * Registered via wire:poll.1000ms in the Blade view.
     */
    public function tick(): void
    {
        $this->refreshCooldown();
    }

    private function refreshCooldown(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $cooldownKey = 'verify_resend_until_' . $user->id;
        $cooldownUntil = session($cooldownKey);

        if ($cooldownUntil && now()->lt($cooldownUntil)) {
            $this->cooldownSeconds = (int) now()->diffInSeconds($cooldownUntil);
        } else {
            $this->cooldownSeconds = 0;
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.resend-verification-email');
    }
}
