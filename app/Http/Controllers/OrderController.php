<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentGatewayException;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UploadPaymentProofRequest;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\NewOrderReceived;
use App\Policies\OrderPolicy;
use App\Services\Payment\PaymentActions;
use App\Services\Payment\PaymentGatewayResolver;
use App\Settings\GeneralSettings;
use App\Support\ArticleLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    /**
     * The order form for one product.
     *
     * Ordering online needs a price to order against, so a quote-on-request
     * item sends the buyer back to its page — where the WhatsApp and email
     * buttons are — instead of showing a form that could not be honoured.
     */
    public function create(Product $product): View|RedirectResponse
    {
        if ($redirect = $this->guardOrderable($product)) {
            return $redirect;
        }

        return view('orders.create', [
            'product' => $product,
            'locale'  => ArticleLocale::current(),
        ]);
    }

    public function store(StoreOrderRequest $request, Product $product): RedirectResponse
    {
        if ($redirect = $this->guardOrderable($product)) {
            return $redirect;
        }

        $data = $request->validated();
        $qty  = (int) $data['qty'];

        // Everything money-related is computed here from the live product, never
        // taken from the form: a hidden price field in the browser is a price
        // the buyer can edit.
        $price    = (float) $product->price;
        $subtotal = $price * $qty;

        $order = Order::place([
            'product_id'            => $product->id,
            'product_name_snapshot' => $product->translate('name', 'id') ?: $product->slug,
            'product_type_snapshot' => $product->type,
            'price_snapshot'        => $price,
            'qty'                   => $qty,
            'subtotal'              => $subtotal,
            // Shipping is quoted by hand after the order lands, so the total is
            // the subtotal until an admin fills it in.
            'total'                 => $subtotal,
            'customer_name'         => $data['customer_name'],
            'customer_email'        => $data['customer_email'],
            'customer_phone'        => $data['customer_phone'],
            'shipping_address'      => $product->isBarang() ? $data['shipping_address'] : null,
            'preferred_date'        => $product->isJasa() ? ($data['preferred_date'] ?? null) : null,
            'notes'                 => $data['notes'] ?? null,
        ]);

        $this->notifyAdmin($order);

        return redirect()->route('order.pending', $order->public_token);
    }

    /**
     * The buyer's own copy of their order.
     *
     * Reached by an unguessable token rather than the readable order number:
     * this page shows a name, a phone number and a home address, and a
     * sequential RB-20260906-0001 in the URL would let anyone walk the list.
     */
    public function pending(Order $order, PaymentGatewayResolver $gateways): View
    {
        $gateway = $gateways->resolve();

        try {
            // Whatever the active gateway needs the page to say. The controller
            // does not know or care which method that is.
            $payment = $gateway->charge($order);
        } catch (PaymentGatewayException $e) {
            // A provider being down must not cost someone the page holding
            // their order number. Say so plainly and leave the contact buttons.
            Log::error('Payment gateway unavailable on the order page', [
                'order_number' => $order->order_number,
                'gateway'      => $e->gateway,
                'error'        => $e->getMessage(),
            ]);

            $payment = [
                'type'    => 'unavailable',
                'gateway' => $e->gateway,
                'name'    => $gateway->name(),
                'message' => $e->userMessage(),
            ];
        }

        return view('orders.pending', [
            'order'   => $order,
            'contact' => $this->contactLinks(),
            'payment' => $payment,
        ]);
    }

    /**
     * Accept the buyer's transfer receipt.
     *
     * Guarded by the same secret token as the page it is posted from, plus a
     * throttle on the route: the endpoint is unauthenticated by design, because
     * the person paying does not have an account.
     */
    public function uploadProof(
        UploadPaymentProofRequest $request,
        Order $order,
        PaymentActions $payments,
    ): RedirectResponse {
        if ($order->isCancelled()) {
            return back()->with('payment_error', 'Pesanan ini sudah dibatalkan, jadi bukti transfer tidak bisa diunggah.');
        }

        if ($order->isPaid()) {
            return back()->with('payment_error', 'Pembayaran pesanan ini sudah lunas — tidak perlu mengunggah bukti lagi.');
        }

        // Private disk, not public: a transfer receipt carries the buyer's own
        // name and account number, and storage/app/public is served to anyone
        // who guesses the filename.
        $path = $request->file('proof')->store(
            PaymentActions::PROOF_DIRECTORY,
            PaymentActions::PROOF_DISK,
        );

        $payments->attachProof($order, $path);

        return back()->with('payment_success', 'Bukti transfer diterima. Kami akan memeriksanya dan mengabari kamu lewat email.');
    }

    /**
     * Serve an uploaded receipt to staff only.
     *
     * The file lives outside the web root precisely so that there is no URL to
     * guess; this is the one door to it, and it is locked to the roles that
     * fulfil orders.
     */
    public function proof(Order $order): StreamedResponse
    {
        abort_unless(OrderPolicy::userIsManager(auth()->user()), 403);
        abort_unless(filled($order->payment_proof), 404);

        $disk = Storage::disk(PaymentActions::PROOF_DISK);

        abort_unless($disk->exists($order->payment_proof), 404);

        // Inline so it previews in the admin panel rather than downloading.
        return $disk->response($order->payment_proof);
    }

    /**
     * Send the "new order" email without letting the mail server take the order
     * down with it.
     *
     * Not queued — this host has no reliable queue worker (see the note in
     * App\Observers\Concerns\RefreshesSitemap), so a queued notification would
     * sit in the jobs table unseen. Sent inline and wrapped: the buyer has
     * already paid nothing and promised nothing, but their order is recorded,
     * and an SMTP timeout must not turn that into a 500.
     */
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
        } catch (\Throwable $e) {
            // Settings not seeded — fall through to the mail config.
        }

        return (string) config('mail.from.address', 'hello@rbeverything.com');
    }

    /** @return array{email: string, whatsapp: ?string} */
    protected function contactLinks(): array
    {
        $email    = $this->adminEmail();
        $whatsapp = null;

        try {
            $number = app(GeneralSettings::class)->whatsapp_number;

            if ($number) {
                $whatsapp = 'https://wa.me/' . preg_replace('/\D/', '', $number);
            }
        } catch (\Throwable $e) {
            // No settings row: the mailto: link above still works.
        }

        return ['email' => $email, 'whatsapp' => $whatsapp];
    }

    /**
     * Reasons an item cannot be ordered through this form, in the order a
     * buyer would hit them.
     */
    protected function guardOrderable(Product $product): ?RedirectResponse
    {
        if (! $product->is_active) {
            abort(404);
        }

        if (! $product->hasPrice()) {
            return redirect()
                ->route('products.show', $product->slug)
                ->with('order_error', 'Produk ini belum punya harga tetap. Hubungi kami dulu untuk penawaran.');
        }

        if (! $product->isInStock()) {
            return redirect()
                ->route('products.show', $product->slug)
                ->with('order_error', 'Stok produk ini sedang habis. Hubungi kami untuk ketersediaan berikutnya.');
        }

        return null;
    }
}
