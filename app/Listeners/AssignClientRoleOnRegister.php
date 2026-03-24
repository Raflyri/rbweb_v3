<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Registered;

class AssignClientRoleOnRegister
{
    /**
     * Secara otomatis assign role 'regular_user' ke setiap user yang baru register
     * melalui panel Client Area, jika user belum memiliki role apapun.
     */
    public function handle(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;

        // Assign 'regular_user' jika belum punya role — menggunakan role yang sudah ada di sistem
        if ($user->roles->isEmpty()) {
            $user->assignRole('regular_user');
        }
    }
}
