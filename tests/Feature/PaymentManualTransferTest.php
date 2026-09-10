<?php

use App\Models\Order;
use App\Models\User;
use App\Notifications\PaymentStatusUpdated;
use App\Services\Payment\ManualTransferGateway;
use App\Services\Payment\PaymentActions;
use App\Services\Payment\PaymentGatewayResolver;
use App\Support\OrderStatus;
use App\Support\PaymentStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    foreach (['super_admin', 'admin', 'regular_user'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }

    // A filled-in account, so the tests exercise the configured path rather
    // than the "not set up yet" fallback.
    config([
        'services.manual_transfer' => [
            'bank_name'      => 'Bank Contoh',
            'account_number' => '1234567890',
            'account_holder' => 'RBeverything',
        ],
    ]);
});

function paymentUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

/*
|--------------------------------------------------------------------------
| Resolver
|--------------------------------------------------------------------------
*/

it('hands out manual transfer by default', function () {
    expect(app(PaymentGatewayResolver::class)->resolve())
        ->toBeInstanceOf(ManualTransferGateway::class);
});

it('falls back to manual transfer when the configured gateway is unknown', function () {
    config(['services.payment.active_gateway' => 'tidak-ada-gateway-ini']);

    // A typo in config must not take checkout down, and a working bank
    // transfer is a safe thing to land on.
    expect(app(PaymentGatewayResolver::class)->resolve())
        ->toBeInstanceOf(ManualTransferGateway::class);
});

it('builds payment instructions from config and the order', function () {
    $order = Order::factory()->create(['subtotal' => 250000, 'total' => 250000]);

    $charge = (new ManualTransferGateway())->charge($order);

    expect($charge['type'])->toBe('manual_transfer')
        ->and($charge['configured'])->toBeTrue()
        ->and($charge['account']['account_number'])->toBe('1234567890')
        ->and($charge['formatted_amount'])->toBe('Rp 250.000')
        ->and($charge['reference'])->toBe($order->order_number)
        // The one instruction that makes a manual transfer matchable at all.
        ->and(implode(' ', $charge['instructions']))->toContain($order->order_number);
});

it('reports itself unconfigured while the placeholders are still in place', function () {
    config([
        'services.manual_transfer' => [
            'bank_name'      => 'ISI_NAMA_BANK',
            'account_number' => 'ISI_NOMOR_REKENING',
            'account_holder' => 'ISI_ATAS_NAMA',
        ],
    ]);

    expect((new ManualTransferGateway())->isConfigured())->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| The buyer's payment page
|--------------------------------------------------------------------------
*/

it('shows the account details and the reference to quote', function () {
    $order = Order::factory()->create(['subtotal' => 250000, 'total' => 250000]);

    get(route('order.pending', $order->public_token))
        ->assertOk()
        ->assertSee('Bank Contoh')
        ->assertSee('1234567890')
        ->assertSee('Rp 250.000')
        ->assertSee($order->order_number);
});

it('never prints placeholder bank details to a buyer', function () {
    config([
        'services.manual_transfer' => [
            'bank_name'      => 'ISI_NAMA_BANK',
            'account_number' => 'ISI_NOMOR_REKENING',
            'account_holder' => 'ISI_ATAS_NAMA',
        ],
    ]);

    $order = Order::factory()->create();

    get(route('order.pending', $order->public_token))
        ->assertOk()
        ->assertDontSee('ISI_NOMOR_REKENING')
        ->assertSee('Instruksi pembayaran akan kami kirimkan langsung');
});

it('warns that the total is not final while shipping is unquoted', function () {
    $order = Order::factory()->create(['shipping_cost' => null]);

    get(route('order.pending', $order->public_token))
        ->assertOk()
        ->assertSee('Ongkos kirim belum masuk hitungan');
});

/*
|--------------------------------------------------------------------------
| Uploading a receipt
|--------------------------------------------------------------------------
*/

it('accepts a transfer receipt and queues it for verification', function () {
    Storage::fake('local');

    $order = Order::factory()->create();

    post(route('order.proof.upload', $order->public_token), [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertRedirect();

    $order->refresh();

    expect($order->payment_status)->toBe(PaymentStatus::MENUNGGU_VERIFIKASI)
        ->and($order->payment_method)->toBe(ManualTransferGateway::KEY)
        ->and($order->payment_proof)->not->toBeNull();

    Storage::disk('local')->assertExists($order->payment_proof);
});

it('stores receipts off the public disk', function () {
    Storage::fake('local');
    Storage::fake('public');

    $order = Order::factory()->create();

    post(route('order.proof.upload', $order->public_token), [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    // A receipt carries the buyer's own name and account number. Anything on
    // the public disk is served to whoever guesses the filename.
    Storage::disk('public')->assertDirectoryEmpty('/');
    expect($order->fresh()->payment_proof)->toStartWith(PaymentActions::PROOF_DIRECTORY);
});

it('replaces an earlier receipt rather than piling them up', function () {
    Storage::fake('local');

    $order = Order::factory()->create();

    post(route('order.proof.upload', $order->public_token), ['proof' => UploadedFile::fake()->image('satu.jpg')]);
    $first = $order->fresh()->payment_proof;

    post(route('order.proof.upload', $order->public_token), ['proof' => UploadedFile::fake()->image('dua.jpg')]);
    $second = $order->fresh()->payment_proof;

    expect($second)->not->toBe($first);
    Storage::disk('local')->assertMissing($first);
    Storage::disk('local')->assertExists($second);
});

it('rejects a file that is not a receipt', function () {
    Storage::fake('local');

    $order = Order::factory()->create();

    post(route('order.proof.upload', $order->public_token), [
        'proof' => UploadedFile::fake()->create('payload.php', 10),
    ])->assertSessionHasErrors('proof');

    expect($order->fresh()->payment_proof)->toBeNull();
});

it('refuses a receipt for an order that is already paid or cancelled', function (string $state) {
    Storage::fake('local');

    $order = $state === 'paid'
        ? Order::factory()->paid()->create()
        : Order::factory()->cancelled()->create();

    post(route('order.proof.upload', $order->public_token), [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertSessionHas('payment_error');

    expect($order->fresh()->payment_proof)->toBeNull();
})->with(['paid', 'cancelled']);

it('clears the rejection note once a new receipt arrives', function () {
    Storage::fake('local');

    $order = Order::factory()->create(['payment_note' => 'Nominal kurang Rp 50.000.']);

    post(route('order.proof.upload', $order->public_token), [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    // The buyer has acted on the note; leaving it up keeps telling them off
    // for a problem they already fixed.
    expect($order->fresh()->payment_note)->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Reading a receipt back
|--------------------------------------------------------------------------
*/

it('lets staff open an uploaded receipt', function (string $role) {
    Storage::fake('local');

    $order = Order::factory()->create();
    post(route('order.proof.upload', $order->public_token), ['proof' => UploadedFile::fake()->image('bukti.jpg')]);

    actingAs(paymentUser($role))
        ->get(route('order.proof', $order->fresh()->public_token))
        ->assertOk();
})->with(['super_admin', 'admin']);

it('refuses the receipt to everyone else, token or not', function () {
    Storage::fake('local');

    $order = Order::factory()->create();
    post(route('order.proof.upload', $order->public_token), ['proof' => UploadedFile::fake()->image('bukti.jpg')]);

    $token = $order->fresh()->public_token;

    // Holding the order link is enough to upload a receipt; it is not enough
    // to read one back.
    get(route('order.proof', $token))->assertForbidden();

    actingAs(paymentUser('regular_user'))
        ->get(route('order.proof', $token))
        ->assertForbidden();
});

it('404s when there is no receipt to show', function () {
    $order = Order::factory()->create();

    actingAs(paymentUser('admin'))
        ->get(route('order.proof', $order->public_token))
        ->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Confirming and rejecting
|--------------------------------------------------------------------------
*/

it('marks an order paid and tells the buyer', function () {
    Notification::fake();

    $order = Order::factory()->create();

    app(PaymentActions::class)->confirm($order);

    $order->refresh();

    expect($order->payment_status)->toBe(PaymentStatus::LUNAS)
        ->and($order->paid_at)->not->toBeNull()
        ->and($order->payment_method)->toBe(ManualTransferGateway::KEY);

    Notification::assertSentOnDemand(PaymentStatusUpdated::class);
});

it('does not move the payment date when an order is confirmed twice', function () {
    Notification::fake();

    $paidAt = now()->subDays(3);
    $order  = Order::factory()->create(['payment_status' => PaymentStatus::LUNAS, 'paid_at' => $paidAt]);

    app(PaymentActions::class)->confirm($order);

    expect($order->fresh()->paid_at->toDateTimeString())->toBe($paidAt->toDateTimeString());
});

it('sends a receipt back with a reason the buyer can act on', function () {
    Notification::fake();

    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::MENUNGGU_VERIFIKASI,
        'payment_proof'  => 'payment-proofs/bukti.jpg',
    ]);

    app(PaymentActions::class)->rejectProof($order, 'Nominal transfer kurang Rp 50.000.');

    $order->refresh();

    expect($order->payment_status)->toBe(PaymentStatus::MENUNGGU)
        ->and($order->payment_note)->toBe('Nominal transfer kurang Rp 50.000.')
        // The file stays: it is the evidence of what was rejected.
        ->and($order->payment_proof)->toBe('payment-proofs/bukti.jpg');

    Notification::assertSentOnDemand(PaymentStatusUpdated::class);
});

it('shows the rejection reason on the buyer page', function () {
    $order = Order::factory()->create(['payment_note' => 'Bukti transfer tidak terbaca.']);

    get(route('order.pending', $order->public_token))
        ->assertOk()
        ->assertSee('Bukti transfer tidak terbaca.');
});

it('still records the payment when the confirmation email fails', function () {
    Notification::shouldReceive('route')->andThrow(new RuntimeException('smtp down'));

    $order = Order::factory()->create();

    app(PaymentActions::class)->confirm($order);

    // The money arrived either way; a dead mail server must not undo that.
    expect($order->fresh()->payment_status)->toBe(PaymentStatus::LUNAS);
});

/*
|--------------------------------------------------------------------------
| Who may confirm
|--------------------------------------------------------------------------
*/

it('lets only staff confirm a payment', function () {
    $order = Order::factory()->create();

    expect(paymentUser('super_admin')->can('confirmPayment', $order))->toBeTrue()
        ->and(paymentUser('admin')->can('confirmPayment', $order))->toBeTrue()
        ->and(paymentUser('regular_user')->can('confirmPayment', $order))->toBeFalse();
});

it('leaves a paid order out of the unpaid queue', function () {
    $unpaid = Order::factory()->create();
    $paid   = Order::factory()->paid()->status(OrderStatus::DIPROSES)->create();

    expect(Order::awaitingPayment()->pluck('id')->all())->toBe([$unpaid->id])
        ->and(Order::paid()->pluck('id')->all())->toBe([$paid->id]);
});
