<?php

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\User;
use App\Policies\ProductPolicy;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    foreach (['super_admin', 'admin', 'premium', 'regular_user'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
});

function productUserWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('lets admins and super admins manage the catalogue', function (string $role) {
    $user    = productUserWithRole($role);
    $product = Product::factory()->create();

    expect(ProductPolicy::userIsManager($user))->toBeTrue()
        ->and($user->can('viewAny', Product::class))->toBeTrue()
        ->and($user->can('create', Product::class))->toBeTrue()
        ->and($user->can('update', $product))->toBeTrue()
        ->and($user->can('delete', $product))->toBeTrue();
})->with(['super_admin', 'admin']);

it('refuses catalogue access to client-area roles', function (string $role) {
    $user    = productUserWithRole($role);
    $product = Product::factory()->create();

    // Pricing and (later) orders are the business itself — an article author
    // in the Client Area must never reach them.
    expect(ProductPolicy::userIsManager($user))->toBeFalse()
        ->and($user->can('viewAny', Product::class))->toBeFalse()
        ->and($user->can('create', Product::class))->toBeFalse()
        ->and($user->can('update', $product))->toBeFalse()
        ->and($user->can('delete', $product))->toBeFalse();
})->with(['premium', 'regular_user']);

it('hides the resource from the navigation of non-admins', function () {
    actingAs(productUserWithRole('regular_user'));

    expect(ProductResource::canViewAny())->toBeFalse();

    actingAs(productUserWithRole('admin'));

    expect(ProductResource::canViewAny())->toBeTrue();
});

it('opens the admin product list for an admin', function () {
    Product::factory()->create();

    actingAs(productUserWithRole('admin'))
        ->get(ProductResource::getUrl('index'))
        ->assertSuccessful();
});

it('renders the create and edit forms without blowing up', function () {
    $product = Product::factory()->create();

    // Cheap insurance for the form schema itself: a wrong component call only
    // surfaces when the page actually renders, not when the class is loaded.
    actingAs(productUserWithRole('admin'))
        ->get(ProductResource::getUrl('create'))
        ->assertSuccessful();

    actingAs(productUserWithRole('admin'))
        ->get(ProductResource::getUrl('edit', ['record' => $product]))
        ->assertSuccessful();
});

it('blocks a client-area user from the admin product list', function () {
    actingAs(productUserWithRole('regular_user'))
        ->get(ProductResource::getUrl('index'))
        // User::canAccessPanel() rejects non-admins for the whole /rbdashboard
        // panel, so the request never even reaches the resource.
        ->assertForbidden();
});

it('never registers the catalogue inside the Client Area panel', function () {
    $clientResources = \Filament\Facades\Filament::getPanel('client-area')->getResources();

    expect($clientResources)->not->toContain(ProductResource::class);
});
