<?php

use App\Contracts\PaymentGateway;
use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Notifications\PaymentStatusUpdated;
use App\Services\Payment\ManualTransferGateway;
use App\Services\Payment\MidtransGateway;
use App\Services\Payment\PaymentGatewayResolver;
use App\Support\PaymentStatus;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\postJson;

const TEST_SERVER_KEY = 'SB-Mid-server-TESTKEY1234567890';

/** Turn Midtrans on the way a real activation would. */
function activateMidtrans(array $overrides = []): void
{
    config(array_merge([
        'services.midtrans.is_active'  => true,
        'services.midtrans.server_key' => TEST_SERVER_KEY,
        'services.payment.active_gateway' => MidtransGateway::KEY,
    ], $overrides));
}

/** The signature Midtrans would send: sha512(order_id + status_code + gross_amount + server_key). */
function midtransSignature(string $orderId, string $statusCode, string $grossAmount, string $serverKey = TEST_SERVER_KEY): string
{
    return hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
}

/** @return array<string, mixed> */
function midtransPayload(Order $order, string $status, array $overrides = []): array
{
    $gross = number_format((float) $order->payableAmount(), 2, '.', '');

    return array_merge([
        'order_id'           => $order->order_number,
        'status_code'        => '200',
        'gross_amount'       => $gross,
        'transaction_status' => $status,
        'signature_key'      => midtransSignature($order->order_number, '200', $gross),
    ], $overrides);
}

/*
|--------------------------------------------------------------------------
| The activation fence
|--------------------------------------------------------------------------
| The whole point of phase 5: the code exists, and nothing can reach it until
| MIDTRANS_IS_ACTIVE is deliberately set to true.
*/

it('ships switched off', function () {
    // Straight from config/services.php, with no environment overrides.
    expect(config('services.midtrans.is_active'))->toBeFalse()
        ->and(config('services.payment.active_gateway'))->toBe(ManualTransferGateway::KEY)
        ->and(app(PaymentGatewayResolver::class)->midtransIsActive())->toBeFalse();
});

it('refuses to select Midtrans while it is inactive, whatever active_gateway says', function () {
    config([
        'services.payment.active_gateway' => MidtransGateway::KEY,
        'services.midtrans.server_key'    => TEST_SERVER_KEY,
        'services.midtrans.is_active'     => false,
    ]);

    // Choosing it is not enough. This is the guard the roadmap asked for
    // explicitly, and it is why a half-finished setup keeps taking transfers.
    expect(app(PaymentGatewayResolver::class)->resolve())
        ->toBeInstanceOf(ManualTransferGateway::class);
});

it('refuses to select Midtrans when the server key is blank', function () {
    config([
        'services.payment.active_gateway' => MidtransGateway::KEY,
        'services.midtrans.is_active'     => true,
        'services.midtrans.server_key'    => '',
    ]);

    expect(app(PaymentGatewayResolver::class)->resolve())
        ->toBeInstanceOf(ManualTransferGateway::class);
});

it('selects Midtrans only when it is switched on and configured', function () {
    activateMidtrans();

    expect(app(PaymentGatewayResolver::class)->resolve())
        ->toBeInstanceOf(MidtransGateway::class);
});

/*
|--------------------------------------------------------------------------
| The webhook
|--------------------------------------------------------------------------
*/

it('does not answer notifications while Midtrans is inactive', function () {
    $order = Order::factory()->create();

    postJson(route('payment.midtrans.notification'), midtransPayload($order, 'settlement'))
        ->assertNotFound();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::MENUNGGU);
});

it('cannot be tricked by a signature computed with a blank server key', function () {
    // The dangerous case: signature verification hashes with the server key,
    // so an empty key would let anyone forge one. Treating a blank key as
    // "inactive" is what closes it.
    config([
        'services.midtrans.is_active'  => true,
        'services.midtrans.server_key' => '',
    ]);

    $order = Order::factory()->create();
    $gross = number_format((float) $order->payableAmount(), 2, '.', '');

    postJson(route('payment.midtrans.notification'), [
        'order_id'           => $order->order_number,
        'status_code'        => '200',
        'gross_amount'       => $gross,
        'transaction_status' => 'settlement',
        'signature_key'      => midtransSignature($order->order_number, '200', $gross, ''),
    ])->assertNotFound();

    expect($order->fresh()->isPaid())->toBeFalse();
});

it('rejects a notification whose signature does not match', function () {
    activateMidtrans();

    $order = Order::factory()->create();

    postJson(route('payment.midtrans.notification'), midtransPayload($order, 'settlement', [
        'signature_key' => str_repeat('a', 128),
    ]))->assertForbidden();

    expect($order->fresh()->isPaid())->toBeFalse();
});

it('rejects a notification with no signature at all', function () {
    activateMidtrans();

    $order = Order::factory()->create();

    postJson(route('payment.midtrans.notification'), midtransPayload($order, 'settlement', [
        'signature_key' => '',
    ]))->assertForbidden();
});

it('404s a notification for an order that does not exist', function () {
    activateMidtrans();

    $gross = '250000.00';

    postJson(route('payment.midtrans.notification'), [
        'order_id'           => 'RB-19700101-9999',
        'status_code'        => '200',
        'gross_amount'       => $gross,
        'transaction_status' => 'settlement',
        'signature_key'      => midtransSignature('RB-19700101-9999', '200', $gross),
    ])->assertNotFound();
});

it('marks an order paid on settlement and tells the buyer', function () {
    Notification::fake();
    activateMidtrans();

    $order = Order::factory()->create();

    postJson(route('payment.midtrans.notification'), midtransPayload($order, 'settlement'))
        ->assertOk();

    $order->refresh();

    expect($order->payment_status)->toBe(PaymentStatus::LUNAS)
        ->and($order->paid_at)->not->toBeNull()
        ->and($order->payment_method)->toBe(MidtransGateway::KEY);

    Notification::assertSentOnDemand(PaymentStatusUpdated::class);
});

it('treats an accepted card capture as paid', function () {
    Notification::fake();
    activateMidtrans();

    $order = Order::factory()->create();

    postJson(route('payment.midtrans.notification'), midtransPayload($order, 'capture', [
        'fraud_status' => 'accept',
    ]))->assertOk();

    expect($order->fresh()->isPaid())->toBeTrue();
});

it('holds a challenged capture for a human instead of banking it', function () {
    Notification::fake();
    activateMidtrans();

    $order = Order::factory()->create();

    postJson(route('payment.midtrans.notification'), midtransPayload($order, 'capture', [
        'fraud_status' => 'challenge',
    ]))->assertOk();

    $order->refresh();

    expect($order->payment_status)->toBe(PaymentStatus::MENUNGGU_VERIFIKASI)
        ->and($order->isPaid())->toBeFalse()
        ->and($order->payment_note)->toContain('challenge');

    Notification::assertNothingSent();
});

it('records a failed payment without touching the order status', function (string $status) {
    Notification::fake();
    activateMidtrans();

    $order = Order::factory()->create();

    postJson(route('payment.midtrans.notification'), midtransPayload($order, $status))
        ->assertOk();

    $order->refresh();

    expect($order->payment_status)->toBe(PaymentStatus::GAGAL)
        ->and($order->payment_note)->toContain($status)
        // Whether the order itself is cancelled is a decision for a person.
        ->and($order->status)->toBe(\App\Support\OrderStatus::BARU);
})->with(['expire', 'cancel', 'deny']);

it('leaves a pending notification alone', function () {
    activateMidtrans();

    $order = Order::factory()->create(['payment_note' => 'Catatan yang tidak boleh hilang.']);

    postJson(route('payment.midtrans.notification'), midtransPayload($order, 'pending'))
        ->assertOk();

    $order->refresh();

    expect($order->payment_status)->toBe(PaymentStatus::MENUNGGU)
        ->and($order->payment_note)->toBe('Catatan yang tidak boleh hilang.');
});

it('does not email the buyer twice when Midtrans retries a settlement', function () {
    Notification::fake();
    activateMidtrans();

    $order   = Order::factory()->create();
    $payload = midtransPayload($order, 'settlement');

    postJson(route('payment.midtrans.notification'), $payload)->assertOk();
    postJson(route('payment.midtrans.notification'), $payload)->assertOk();

    // Midtrans retries until it gets a 200, so the same settlement arrives
    // more than once. One payment, one email.
    Notification::assertSentOnDemandTimes(PaymentStatusUpdated::class, 1);
});

it('keeps the original payment date when a retry arrives later', function () {
    Notification::fake();
    activateMidtrans();

    $order   = Order::factory()->create();
    $payload = midtransPayload($order, 'settlement');

    postJson(route('payment.midtrans.notification'), $payload)->assertOk();
    $paidAt = $order->fresh()->paid_at;

    $this->travel(2)->hours();
    postJson(route('payment.midtrans.notification'), $payload)->assertOk();

    expect($order->fresh()->paid_at->toDateTimeString())->toBe($paidAt->toDateTimeString());
});

/*
|--------------------------------------------------------------------------
| Gateway failures never reach the buyer as a 500
|--------------------------------------------------------------------------
*/

it('refuses to charge without a server key rather than calling the API', function () {
    config(['services.midtrans.server_key' => '']);

    $order = Order::factory()->create();

    expect(fn () => (new MidtransGateway())->charge($order))
        ->toThrow(PaymentGatewayException::class);
});

it('shows a readable message when the gateway is down', function () {
    // A stand-in for "Midtrans is unreachable", so the test never touches the
    // network to prove the page survives it.
    app()->bind(PaymentGatewayResolver::class, fn () => new class extends PaymentGatewayResolver
    {
        public function resolve(): PaymentGateway
        {
            return new class implements PaymentGateway
            {
                public function key(): string
                {
                    return MidtransGateway::KEY;
                }

                public function name(): string
                {
                    return 'Midtrans';
                }

                public function charge(Order $order): array
                {
                    throw new PaymentGatewayException('connection timed out', MidtransGateway::KEY);
                }
            };
        }
    });

    $order = Order::factory()->create();

    $this->get(route('order.pending', $order->public_token))
        ->assertOk()
        ->assertSee('Pembayaran online sedang tidak tersedia')
        // The order number is still on the page — that is the thing the buyer
        // needs in order to ask about this.
        ->assertSee($order->order_number)
        ->assertDontSee('connection timed out');
});

/*
|--------------------------------------------------------------------------
| The activation check
|--------------------------------------------------------------------------
*/

it('reports honestly that there is nothing to test yet', function () {
    config(['services.midtrans.server_key' => '']);

    $this->artisan('midtrans:test-connection')
        ->expectsOutputToContain('MIDTRANS_SERVER_KEY belum diisi')
        ->assertFailed();
});
