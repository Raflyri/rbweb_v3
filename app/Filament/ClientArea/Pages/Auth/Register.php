<?php

namespace App\Filament\ClientArea\Pages\Auth;

use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;

class Register extends BaseRegister
{
    /**
     * Override handleRegistration untuk menetapkan role 'regular_user' segera
     * setelah user dibuat — sebelum canAccessPanel dipanggil oleh Filament.
     * Ini lebih andal dari Event Listener untuk Filament v3.
     *
     * @param array<string, mixed> $data
     */
    protected function handleRegistration(array $data): Model
    {
        /** @var \App\Models\User $user */
        $user = parent::handleRegistration($data);

        // Assign role hanya jika user belum punya role apapun
        if ($user->roles->isEmpty()) {
            $user->assignRole('regular_user');
        }

        return $user;
    }
}
