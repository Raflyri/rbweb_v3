<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('debug:roles', function () {
    User::with('roles')->get()->each(function ($user) {
        $roles = $user->getRoleNames()->implode(', ') ?: '(no roles)';
        $this->line("{$user->email} => [{$roles}]");
    });
})->purpose('Debug: list all users and their roles');

Artisan::command('fix:roles', function () {
    $users = User::whereDoesntHave('roles')->get();
    foreach ($users as $user) {
        /** @var User $user */
        $user->assignRole('regular_user');
        $this->line("Assigned regular_user to: {$user->email}");
    }
    $this->info('Done.');
})->purpose('Assign regular_user role to all users missing a role');
