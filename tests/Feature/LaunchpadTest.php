<?php

use App\Models\LaunchpadLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    Role::firstOrCreate(['name' => 'premium',      'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'regular_user', 'guard_name' => 'web']);
});

// ── 1. Inactive cards are never shown ────────────────────────────────────────
it('does not show inactive launchpad links', function () {
    LaunchpadLink::create([
        'title'     => 'Hidden Tool',
        'url'       => 'https://hidden.rbeverything.com',
        'is_active' => false,
    ]);

    $visible = LaunchpadLink::active()->get();
    expect($visible)->toHaveCount(0);
});

// ── 2. Public card (no permission) is visible to all ─────────────────────────
it('shows public launchpad cards to any authenticated user', function () {
    LaunchpadLink::create([
        'title'     => 'Public Tool',
        'url'       => 'https://base64tools.rbeverything.com',
        'is_active' => true,
    ]);

    $regularUser = User::factory()->create();
    $regularUser->assignRole('regular_user');


    $links = LaunchpadLink::active()->get()->filter(function ($link) use ($regularUser) {
        return ! $link->required_permission || $regularUser->can($link->required_permission);
    });

    expect($links)->toHaveCount(1);
});

// ── 3. Permission-gated card is hidden from regular_user ─────────────────────
it('hides premium-gated cards from regular users', function () {
    $permission = Permission::firstOrCreate(['name' => 'access_premium_tools', 'guard_name' => 'web']);

    LaunchpadLink::create([
        'title'               => 'Premium Tool',
        'url'                 => 'https://premium.rbeverything.com',
        'is_active'           => true,
        'required_permission' => 'access_premium_tools',
    ]);

    $regularUser = User::factory()->create();
    $regularUser->assignRole('regular_user');

    $accessible = LaunchpadLink::active()->get()->filter(function ($link) use ($regularUser) {
        return ! $link->required_permission || $regularUser->can($link->required_permission);
    });

    expect($accessible)->toHaveCount(0);
});

// ── 4. Premium user can see permission-gated card ─────────────────────────────
it('shows premium-gated cards to users with the required permission', function () {
    $permission = Permission::firstOrCreate(['name' => 'access_premium_tools', 'guard_name' => 'web']);

    $premiumRole = Role::firstOrCreate(['name' => 'premium', 'guard_name' => 'web']);
    $premiumRole->givePermissionTo($permission);

    LaunchpadLink::create([
        'title'               => 'Premium Tool',
        'url'                 => 'https://premium.rbeverything.com',
        'is_active'           => true,
        'required_permission' => 'access_premium_tools',
    ]);

    $premiumUser = User::factory()->create();
    $premiumUser->assignRole('premium');

    $accessible = LaunchpadLink::active()->get()->filter(function ($link) use ($premiumUser) {
        return ! $link->required_permission || $premiumUser->can($link->required_permission);
    });

    expect($accessible)->toHaveCount(1);
});
