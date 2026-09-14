<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Notifications\NewOrderReceived;
use App\Services\Cart\CartService;
use App\Services\Payment\ManualTransferGateway;
use App\Services\Payment\MidtransGateway;
use App\Services\Payment\PaymentGatewayResolver;
use App\Settings\GeneralSettings;
use App\Support\ProductType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cart,
        protected PaymentGatewayResolver $gateways
    ) {}

    public function index(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('cart_info', 'Keranjang belanja kamu masih kosong.');
        }

        return view('checkout.index', [
            'items'            => $this->cart->items(),
            'count'            => $this->cart->count(),
            'subtotal'         => $this->cart->formattedSubtotal(),
            'subtotalNumeric'  => $this->cart->subtotal(),
            'hasPhysicalItems' => $this->cart->hasPhysicalItems(),
            'paymentMethods'   => $this->availablePaymentMethods(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Bot honeypot: fail silently or redirect back if filled
        if (filled($request->input('website'))) {
            return redirect()->route('home');
        }

        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('cart_error', 'Keranjang belanja kamu kosong.');
        }

        $hasPhysical = $this->cart->hasPhysicalItems();

        $rules = [
            'customer_name'    => ['required', 'string', 'max:120'],
            'customer_email'   => ['required', 'email', 'max:190'],
            'customer_phone'   => ['required', 'string', 'max:40'],
            'shipping_address' => [$hasPhysical ? 'required' : 'nullable', 'string', 'max:1000'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'payment_method'   => ['required', 'string', 'max:50'],
        ];

        $data = $request->validate($rules);

        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal();
        $firstItem = $items->first();
        $itemCount = $items->count();

        $nameSnapshot = $itemCount === 1
            ? (string) $firstItem['name']
            : $firstItem['name'] . ' (+' . ($itemCount - 1) . ' produk lainnya)';

        $paymentMethodInput = (string) $data['payment_method'];
        $isMidtransChannel = array_key_exists($paymentMethodInput, MidtransGateway::ALL_CHANNELS);
        $orderPaymentMethod = $isMidtransChannel ? MidtransGateway::KEY : ManualTransferGateway::KEY;

        $order = Order::place([
            'product_id'            => $firstItem['product_id'] ?? null,
            'product_name_snapshot' => $nameSnapshot,
            'product_type_snapshot' => $hasPhysical ? ProductType::BARANG : ProductType::JASA,
            'price_snapshot'        => $itemCount === 1 ? (float) $firstItem['price'] : $subtotal,
            'qty'                   => $this->cart->count(),
            'subtotal'              => $subtotal,
            'total'                 => $subtotal,
            'customer_name'         => $data['customer_name'],
            'customer_email'        => $data['customer_email'],
            'customer_phone'        => $data['customer_phone'],
            'shipping_address'      => $hasPhysical ? ($data['shipping_address'] ?? null) : null,
            'notes'                 => $data['notes'] ?? null,
            'payment_method'        => $orderPaymentMethod,
        ]);

        // Insert items into order_items table
        foreach ($items as $item) {
            $order->items()->create([
                'product_id'            => $item['product_id'],
                'product_name_snapshot' => $item['name'],
                'product_type_snapshot' => $item['type'] ?? ProductType::BARANG,
                'price_snapshot'        => (float) $item['price'],
                'qty'                   => (int) $item['qty'],
                'subtotal'              => (float) ($item['price'] * $item['qty']),
            ]);
        }

        // If a Midtrans channel was selected and Midtrans is active, charge it immediately
        if ($isMidtransChannel && $this->gateways->midtransIsActive()) {
            try {
                $midtrans = new MidtransGateway();
                $midtrans->chargeChannel($order, $paymentMethodInput);
            } catch (PaymentGatewayException $e) {
                Log::warning('Auto-charge channel failed on checkout, fallback to selection', [
                    'order_number' => $order->order_number,
                    'channel'      => $paymentMethodInput,
                    'error'        => $e->getMessage(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Unexpected error charging channel on checkout', [
                    'order_number' => $order->order_number,
                    'channel'      => $paymentMethodInput,
                    'error'        => $e->getMessage(),
                ]);
            }
        }

        // Clear cart
        $this->cart->clear();

        // Notify admin
        $this->notifyAdmin($order);

        return redirect()->route('order.pending', $order->public_token)
            ->with('payment_info', 'Pesanan kamu berhasil dibuat. Silakan selesaikan pembayaran.');
    }

    /**
     * List of available payment methods for checkout selection.
     *
     * @return array<string, array<string, mixed>>
     */
    public function availablePaymentMethods(): array
    {
        $methods = [];

        if ($this->gateways->midtransIsActive()) {
            $midtrans = new MidtransGateway();
            $channels = $midtrans->enabledChannels();

            foreach ($channels as $id => $info) {
                $methods[$id] = [
                    'id'          => $id,
                    'type'        => 'midtrans',
                    'name'        => $info['name'],
                    'label'       => $info['label'],
                    'description' => $info['description'],
                    'badge'       => $info['badge'] ?? null,
                    'category'    => $info['category'] ?? 'va',
                ];
            }
        }

        $manual = new ManualTransferGateway();
        $account = $manual->account();
        $methods[ManualTransferGateway::KEY] = [
            'id'          => ManualTransferGateway::KEY,
            'type'        => 'manual',
            'name'        => 'Transfer Bank Manual',
            'label'       => 'Transfer Bank Manual' . (filled($account['bank_name']) && $manual->isConfigured() ? ' (' . $account['bank_name'] . ')' : ''),
            'description' => 'Transfer manual ke rekening bank kami dan unggah bukti transfer.',
            'badge'       => 'Verifikasi Manual',
            'category'    => 'manual',
            'account'     => $account,
            'configured'  => $manual->isConfigured(),
        ];

        return $methods;
    }

    protected function notifyAdmin(Order $order): void
    {
        try {
            Notification::route('mail', $this->adminEmail())
                ->notify(new NewOrderReceived($order));
        } catch (\Throwable $e) {
            Log::error('New order email failed to send', [
                'order_number' => $order->order_number,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    protected function adminEmail(): string
    {
        try {
            $configured = app(GeneralSettings::class)->contact_email;

            if ($configured) {
                return $configured;
            }
        } catch (\Throwable) {
        }

        return (string) config('mail.from.address', 'hello@rbeverything.com');
    }
}
