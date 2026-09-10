<?php

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Policies\OrderPolicy;
use App\Support\OrderStatus;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    foreach (['super_admin', 'admin', 'premium', 'regular_user'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
});

function orderUserWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('lets admins and super admins work through orders', function (string $role) {
    $user  = orderUserWithRole($role);
    $order = Order::factory()->create();

    expect(OrderPolicy::userIsManager($user))->toBeTrue()
        ->and($user->can('viewAny', Order::class))->toBeTrue()
        ->and($user->can('view', $order))->toBeTrue()
        ->and($user->can('update', $order))->toBeTrue();
})->with(['super_admin', 'admin']);

it('keeps orders away from client-area roles', function (string $role) {
    $user  = orderUserWithRole($role);
    $order = Order::factory()->create();

    // Orders carry a buyer's name, phone number and home address.
    expect(OrderPolicy::userIsManager($user))->toBeFalse()
        ->and($user->can('viewAny', Order::class))->toBeFalse()
        ->and($user->can('view', $order))->toBeFalse()
        ->and($user->can('update', $order))->toBeFalse();
})->with(['premium', 'regular_user']);

it('never lets an order be typed in by hand', function () {
    $admin = orderUserWithRole('admin');

    // Orders come from the public form. A hand-made one would have no buyer
    // behind it and no snapshot worth trusting.
    expect($admin->can('create', Order::class))->toBeFalse()
        ->and(OrderResource::canCreate())->toBeFalse();
});

it('lets only a super admin delete an order', function () {
    $order = Order::factory()->create();

    expect(orderUserWithRole('admin')->can('delete', $order))->toBeFalse()
        ->and(orderUserWithRole('super_admin')->can('delete', $order))->toBeTrue();
});

it('opens the order screens for an admin', function () {
    $order = Order::factory()->create();

    actingAs(orderUserWithRole('admin'))
        ->get(OrderResource::getUrl('index'))
        ->assertSuccessful();

    actingAs(orderUserWithRole('admin'))
        ->get(OrderResource::getUrl('edit', ['record' => $order]))
        ->assertSuccessful();
});

it('renders the payment section for an order that has a receipt', function () {
    // Exercises the proof preview components, which only appear once a file
    // has been uploaded and would otherwise never be rendered by a test.
    $order = Order::factory()->create([
        'payment_status' => \App\Support\PaymentStatus::MENUNGGU_VERIFIKASI,
        'payment_proof'  => 'payment-proofs/bukti.jpg',
    ]);

    actingAs(orderUserWithRole('admin'))
        ->get(OrderResource::getUrl('edit', ['record' => $order]))
        ->assertSuccessful()
        ->assertSee(route('order.proof', $order->public_token), false);
});

it('blocks a client-area user from the order screens', function () {
    actingAs(orderUserWithRole('regular_user'))
        ->get(OrderResource::getUrl('index'))
        ->assertForbidden();
});

it('never registers orders inside the Client Area panel', function () {
    $clientResources = \Filament\Facades\Filament::getPanel('client-area')->getResources();

    expect($clientResources)->not->toContain(OrderResource::class);
});

it('badges the navigation with the count of untouched orders', function () {
    Order::factory()->count(2)->create();
    Order::factory()->status(OrderStatus::SELESAI)->create();

    actingAs(orderUserWithRole('admin'));

    expect(OrderResource::getNavigationBadge())->toBe('2');
});

it('leaves the badge off when there is nothing new', function () {
    Order::factory()->status(OrderStatus::SELESAI)->create();

    actingAs(orderUserWithRole('admin'));

    expect(OrderResource::getNavigationBadge())->toBeNull();
});
