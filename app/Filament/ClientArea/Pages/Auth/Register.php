<?php

namespace App\Filament\ClientArea\Pages\Auth;

use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

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

        // Pastikan role 'regular_user' ada, lalu assign jika user belum punya role
        Role::firstOrCreate(['name' => 'regular_user', 'guard_name' => 'web']);

        if ($user->roles->isEmpty()) {
            $user->assignRole('regular_user');
        }

        return $user;
    }
}
