<?php

use App\Models\User;
use App\Filament\Pages\ManageGeneralSettings;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\{actingAs, get};

beforeEach(function () {
    // Ensure roles exist in the in-memory database
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
});

it('prevents Admin from accessing Global Settings', function () {
    $admin = User::factory()->create();
    $admin->assignRole(['Admin', 'admin']);

    actingAs($admin)
        ->get(ManageGeneralSettings::getUrl())
        ->assertForbidden();
});

it('allows Super Admin to access Global Settings', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(['Super Admin', 'super_admin']);

    actingAs($superAdmin)
        ->get(ManageGeneralSettings::getUrl())
        ->assertSuccessful();
});
