<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\NewOrderReceived;
use App\Settings\GeneralSettings;
use App\Support\ArticleLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

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
    public function pending(Order $order): View
    {
        return view('orders.pending', [
            'order'   => $order,
            'contact' => $this->contactLinks(),
        ]);
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
