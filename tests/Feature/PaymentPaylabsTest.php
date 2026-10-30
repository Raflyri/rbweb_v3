<?php

use App\Contracts\PaymentGateway;
use App\Models\Order;
use App\Services\Payment\ManualTransferGateway;
use App\Services\Payment\PaylabsGateway;
use App\Services\Payment\PaymentGatewayResolver;
use App\Support\PaymentStatus;

use function Pest\Laravel\postJson;

const PAYLABS_TEST_PRIVKEY = <<<'EOD'
-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCqV2kQCo9DJyaD
xXlM+gep5pLEufT9k/n3jrMAxljXV+UimXzFqi0Yp/H5/RnqAryhRIKDOUjQ5cCG
ksIZ5jK7UKEBCweQ423z63kr4C5yzL5gBkToaE1zBvjxtGmb6Hi5nFXNJz2HSIaJ
9E73Vo+JA2vCCjLntR9svt5tAXTQHdGheRaphpVLhdC+pLancaYFwtzS+6guU6I4
OUulfX+w+f1lSriTqNeKStafvbYUy37wd3Vast9fBdbRQfKp8VhnjglpJ0vhTqjT
+uUcqFHPPYTAWGXnM8XLFz3LQ7eRES5WTkwskDUiG/ipcEt6FW6FYirsiL9bCBtK
LtKKnj4TAgMBAAECggEAG7oeDDy7ycGDVRWoGK3mNh37AUdc/TMWEC7L17IcNJ6b
r0Bup+TQcbVR/6WBj1l3XNoVqHMtoBtAdbyebbAjzTZi+Wl93Mli/2jykKrCxMWr
rgkMibat91hI3gcbiFjp4G9LJBlhZuDuTWGqwjtLzTtKUgvDnNBwV2ImOyDD4+WC
iH0fgmTM1AcHc07z43xINyP2ekigTA14NlRCYdNYmuxKxVu6FMDVjNDCBuqBhfB8
+qxg3wqwwwuAfdWyrvl38i/wb2WN5URpt4iP8gFxEDM7BY0D6G46obemsv1Np9WK
eQdUhZK969HaW1MdyLo6ne4bpNEP9Upe5Xe10eim0QKBgQDff3j2E8W9UQoCr/8C
XZ1BA7IBfRE9uNQhYbJES0JdYf0yqfAoCscE7qxN+6aKRmNHdwK/IWaFXI9ZFyi0
dQ7feAqojq6yMtbTjYf2R31fotj5iy1qOlhN4BfUZFCJ5Afenj/MXVyj6cSF2bIC
zW6yBTHu58Pe6koTVE0CbzSEuwKBgQDDHP5DdRGlkkG+LbkCfI6LVp0XwYsI7IAg
/OFrTx5ZsmeUzSHU1CpDvd9nSK+wtgySPPDlJTTnCvGYPo/HiZ33/vZmjorfIJYD
wIr1je7izrTb4qedzSOUhknRmdtpxV+VfaKFKcdTVyTloONp7llNprxDd73HXsrH
BLjdqLFCiQKBgQCQ7BeDeU6/c80J8RVqc0mE3uCOFosXesIS40a1AhgGdYO5Vnhj
fpmOmTUhXAyhnQLDVGhzhzTzRRGus1WxrjX2seA3bE3bPxwOEbgX+kNRwEbxiU3p
k+uuk6DgGCwhPbvcnsC/w8pOf0bvhDnpl0wwIy49vAtGgKWH4yIMcg+cAQKBgGou
EA0D25ugMVjepw08XZJki08PS6uKkNvi4oW0Y87qcMYgsCWTZIDDBp29h6cCG2tF
8M6y5cxKP1nbygEL2kTIoq0DK33rzNez46toFzWkyfXTreYJajB8uBVxxl8UQ0Um
Pth6+PdYuEKiwL+4Ly876r5InqWt52pRHo26LOUJAoGBAL20FHKYdJGvr10e/7ok
2+wg7/qYtdI4Q0Plj8iEw8YScI7Un7sPgmd4PvZj24BQBijaDETkjRYwO02gOgTh
pSoZtyPNsXzywYAGmcl1aN9pt0tZ7Fxqd+AYtA7CMG16dXrrokhDvg5hy57H3fHY
zvmnn8cOvbQJ9QNdDPdnaoDn
-----END PRIVATE KEY-----
EOD;

const PAYLABS_TEST_PUBKEY = <<<'EOD'
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqldpEAqPQycmg8V5TPoH
qeaSxLn0/ZP5946zAMZY11flIpl8xaotGKfx+f0Z6gK8oUSCgzlI0OXAhpLCGeYy
u1ChAQsHkONt8+t5K+Aucsy+YAZE6GhNcwb48bRpm+h4uZxVzSc9h0iGifRO91aP
iQNrwgoy57UfbL7ebQF00B3RoXkWqYaVS4XQvqS2p3GmBcLc0vuoLlOiODlLpX1/
sPn9ZUq4k6jXikrWn722FMt+8Hd1WrLfXwXW0UHyqfFYZ44JaSdL4U6o0/rlHKhR
zz2EwFhl5zPFyxc9y0O3kREuVk5MLJA1Ihv4qXBLehVuhWIq7Ii/WwgbSi7Sip4+
EwIDAQAB
-----END PUBLIC KEY-----
EOD;

const PAYLABS_TEST_MERCHANT_ID = '0010001';

function activatePaylabs(array $overrides = []): void
{
    config(array_merge([
        'services.paylabs.is_active'   => true,
        'services.paylabs.merchant_id' => PAYLABS_TEST_MERCHANT_ID,
        'services.paylabs.private_key' => PAYLABS_TEST_PRIVKEY,
        'services.paylabs.public_key'  => PAYLABS_TEST_PUBKEY,
        'services.payment.active_gateway' => PaylabsGateway::KEY,
    ], $overrides));
}

function paylabsSign(string $method, string $endpoint, string $rawBody, string $timestamp): string
{
    $bodyHash = hash('sha256', $rawBody);
    $stringToSign = strtoupper($method) . ':' . $endpoint . ':' . strtolower($bodyHash) . ':' . $timestamp;
    $binarySignature = '';
    openssl_sign($stringToSign, $binarySignature, PAYLABS_TEST_PRIVKEY, OPENSSL_ALGO_SHA256);
    return base64_encode($binarySignature);
}

it('ships paylabs switched off by default', function () {
    expect(config('services.paylabs.is_active'))->toBeFalse()
        ->and(app(PaymentGatewayResolver::class)->paylabsIsActive())->toBeFalse();
});

it('refuses to select Paylabs while it is inactive, whatever active_gateway says', function () {
    config([
        'services.payment.active_gateway' => PaylabsGateway::KEY,
        'services.paylabs.merchant_id'    => PAYLABS_TEST_MERCHANT_ID,
        'services.paylabs.is_active'      => false,
    ]);

    expect(app(PaymentGatewayResolver::class)->resolve())
        ->toBeInstanceOf(ManualTransferGateway::class);
});

it('refuses to select Paylabs when merchant_id is blank', function () {
    config([
        'services.payment.active_gateway' => PaylabsGateway::KEY,
        'services.paylabs.is_active'      => true,
        'services.paylabs.merchant_id'    => '',
    ]);

    expect(app(PaymentGatewayResolver::class)->resolve())
        ->toBeInstanceOf(ManualTransferGateway::class);
});

it('selects Paylabs when switched on and configured', function () {
    activatePaylabs();

    expect(app(PaymentGatewayResolver::class)->resolve())
        ->toBeInstanceOf(PaylabsGateway::class);
});

it('generates and verifies RSA-SHA256 signature correctly', function () {
    $gateway = new PaylabsGateway();
    $body = ['merchantId' => '0010001', 'amount' => '50000.00'];
    $timestamp = '2026-03-30T12:00:00.000+07:00';
    $endpoint = '/payment/v2.3/qris/create';

    $signature = $gateway->generateSignature('POST', $endpoint, $body, $timestamp, PAYLABS_TEST_PRIVKEY);
    expect($signature)->toBeString()->not->toBeEmpty();

    $minified = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $isValid = $gateway->verifySignature('POST', $endpoint, $minified, $timestamp, $signature, PAYLABS_TEST_PUBKEY);
    expect($isValid)->toBeTrue();

    // Tampered body should fail verification
    $isTampered = $gateway->verifySignature('POST', $endpoint, '{"tampered":true}', $timestamp, $signature, PAYLABS_TEST_PUBKEY);
    expect($isTampered)->toBeFalse();
});

it('does not answer notifications while Paylabs is inactive', function () {
    $order = Order::factory()->create();

    $payload = [
        'merchantTradeNo' => $order->order_number,
        'platformTradeNo' => 'PL-12345',
        'errCode'         => '0',
        'status'          => '02',
        'amount'          => number_format((float) $order->payableAmount(), 2, '.', ''),
    ];
    $rawBody = json_encode($payload);
    $timestamp = '2026-03-30T12:00:00.000+07:00';
    $sig = paylabsSign('POST', '/payment/paylabs/notification', $rawBody, $timestamp);

    postJson('/payment/paylabs/notification', $payload, [
        'X-TIMESTAMP'  => $timestamp,
        'X-SIGNATURE'  => $sig,
        'X-PARTNER-ID' => PAYLABS_TEST_MERCHANT_ID,
        'X-REQUEST-ID' => 'REQ-123',
    ])->assertStatus(404);
});

it('rejects notifications with forged signatures', function () {
    activatePaylabs();
    $order = Order::factory()->create();

    $payload = [
        'merchantTradeNo' => $order->order_number,
        'platformTradeNo' => 'PL-12345',
        'errCode'         => '0',
        'status'          => '02',
        'amount'          => number_format((float) $order->payableAmount(), 2, '.', ''),
    ];

    postJson('/payment/paylabs/notification', $payload, [
        'X-TIMESTAMP'  => '2026-03-30T12:00:00.000+07:00',
        'X-SIGNATURE'  => base64_encode('invalid-signature-bytes'),
        'X-PARTNER-ID' => PAYLABS_TEST_MERCHANT_ID,
        'X-REQUEST-ID' => 'REQ-123',
    ])->assertStatus(403);
});

it('confirms order when a valid Paylabs notification is received', function () {
    activatePaylabs();
    $order = Order::factory()->create();

    $amount = number_format((float) $order->payableAmount(), 2, '.', '');
    $payload = [
        'merchantTradeNo' => $order->order_number,
        'platformTradeNo' => 'PL-TXN-998877',
        'paymentType'     => 'QRIS',
        'errCode'         => '0',
        'status'          => '02',
        'amount'          => $amount,
    ];
    $rawBody = json_encode($payload);
    $timestamp = '2026-03-30T12:00:00.000+07:00';
    $sig = paylabsSign('POST', '/payment/paylabs/notification', $rawBody, $timestamp);

    $response = postJson('/payment/paylabs/notification', $payload, [
        'X-TIMESTAMP'  => $timestamp,
        'X-SIGNATURE'  => $sig,
        'X-PARTNER-ID' => PAYLABS_TEST_MERCHANT_ID,
        'X-REQUEST-ID' => 'REQ-TEST-1',
    ]);

    $response->assertStatus(200);
    expect($response->json('errCode'))->toBe('0');

    $order->refresh();
    expect($order->isPaid())->toBeTrue()
        ->and($order->payment_method)->toBe(PaylabsGateway::KEY)
        ->and($order->paylabs_transaction_id)->toBe('PL-TXN-998877');
});

it('flags underpayment when paid amount is less than order total', function () {
    activatePaylabs();
    $order = Order::factory()->create(['subtotal' => 100000, 'total' => 100000]);

    $payload = [
        'merchantTradeNo' => $order->order_number,
        'platformTradeNo' => 'PL-UNDERPAY',
        'paymentType'     => 'QRIS',
        'errCode'         => '0',
        'status'          => '02',
        'amount'          => '50000.00', // Underpayment!
    ];
    $rawBody = json_encode($payload);
    $timestamp = '2026-03-30T12:00:00.000+07:00';
    $sig = paylabsSign('POST', '/payment/paylabs/notification', $rawBody, $timestamp);

    $response = postJson('/payment/paylabs/notification', $payload, [
        'X-TIMESTAMP'  => $timestamp,
        'X-SIGNATURE'  => $sig,
        'X-PARTNER-ID' => PAYLABS_TEST_MERCHANT_ID,
        'X-REQUEST-ID' => 'REQ-TEST-2',
    ]);

    $response->assertStatus(200);
    $order->refresh();
    expect($order->isPaid())->toBeFalse()
        ->and($order->payment_status)->toBe(PaymentStatus::MENUNGGU_VERIFIKASI);
});

it('renders QRIS QR code on pending order page for paylabs_qris channel', function () {
    activatePaylabs();
    $order = Order::factory()->create([
        'payment_method'          => PaylabsGateway::KEY,
        'paylabs_payment_type'    => PaylabsGateway::CHANNEL_QRIS,
        'paylabs_transaction_id'  => 'PL-TEST-QR-123',
        'paylabs_payment_payload' => [
            'status_code'        => '200',
            'transaction_id'     => 'PL-TEST-QR-123',
            'channel'            => PaylabsGateway::CHANNEL_QRIS,
            'qr_string'          => '00020101021226660014ID.LINKAJA.WWW0118936009180000000001',
            'qr_url'             => 'https://pay.paylabs.co.id/qris/image/PL-TEST-QR-123.png',
            'gross_amount'       => '50000.00',
            'is_simulated'       => true,
        ],
    ]);

    $response = test()->get(route('order.pending', $order->public_token));

    $response->assertStatus(200);
    $response->assertSee('coreapi-qr-image');
    $response->assertSee('https://pay.paylabs.co.id/qris/image/PL-TEST-QR-123.png');
    $response->assertSee('Simpan / Buka QR Code');
});

