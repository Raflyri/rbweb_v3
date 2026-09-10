<?php

namespace App\Contracts;

use App\Models\Order;

/**
 * One way of getting paid.
 *
 * The point of this interface is that OrderController never learns which
 * method is in use. It asks the resolver for a gateway, calls charge(), and
 * renders whatever comes back — so adding Midtrans later is a new class and
 * one line in PaymentGatewayResolver, not a rewrite of the order flow.
 */
interface PaymentGateway
{
    /**
     * Stable machine name, stored on the order as payment_method.
     *
     * Separate from name(): the label can be reworded for buyers at any time,
     * but this string ends up in the database and must not move.
     */
    public function key(): string;

    /** Display name shown to the buyer, e.g. "Transfer Bank Manual". */
    public function name(): string;

    /**
     * Everything the payment view needs to tell the buyer what to do.
     *
     * The shape varies by gateway, and 'type' says which shape it is:
     *
     *   ['type' => 'manual_transfer', 'account' => [...], 'instructions' => [...]]
     *   ['type' => 'redirect',        'url' => 'https://...']
     *
     * Common keys every gateway returns: type, gateway, name, amount,
     * formatted_amount, reference, configured.
     *
     * @return array<string, mixed>
     */
    public function charge(Order $order): array;
}
