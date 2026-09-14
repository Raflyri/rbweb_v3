<?php

namespace App\Services\Cart;

use App\Models\Order;
use App\Models\Product;
use App\Support\ProductType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CartService
{
    protected const SESSION_KEY = 'rb_shopping_cart';

    /**
     * Get all items currently in the cart as a Collection of arrays/objects.
     *
     * @return Collection<int, array{
     *     product_id: int,
     *     slug: string,
     *     name: string,
     *     price: float,
     *     formatted_price: string,
     *     qty: int,
     *     subtotal: float,
     *     formatted_subtotal: string,
     *     type: string,
     *     thumbnail: ?string,
     *     tracks_stock: bool,
     *     stock: ?int
     * }>
     */
    public function items(): Collection
    {
        $raw = Session::get(self::SESSION_KEY, []);

        return collect($raw)->map(function ($item) {
            $price = (float) ($item['price'] ?? 0);
            $qty = (int) ($item['qty'] ?? 1);
            $subtotal = $price * $qty;

            return array_merge($item, [
                'formatted_price'    => Order::formatRupiah($price),
                'subtotal'           => $subtotal,
                'formatted_subtotal' => Order::formatRupiah($subtotal),
            ]);
        });
    }

    /**
     * Total quantity of items in the cart.
     */
    public function count(): int
    {
        return (int) $this->items()->sum('qty');
    }

    /**
     * Number of distinct products in the cart.
     */
    public function distinctCount(): int
    {
        return $this->items()->count();
    }

    /**
     * Total price of all items in the cart.
     */
    public function subtotal(): float
    {
        return (float) $this->items()->sum(function ($item) {
            return ((float) $item['price']) * ((int) $item['qty']);
        });
    }

    public function formattedSubtotal(): string
    {
        return Order::formatRupiah($this->subtotal());
    }

    public function isEmpty(): bool
    {
        return $this->items()->isEmpty();
    }

    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Check whether any item in the cart is physical goods (requires shipping address).
     */
    public function hasPhysicalItems(): bool
    {
        return $this->items()->contains(function ($item) {
            return ($item['type'] ?? '') === ProductType::BARANG;
        });
    }

    /**
     * Add a product to the cart.
     */
    public function add(Product $product, int $qty = 1): void
    {
        if ($qty < 1) {
            $qty = 1;
        }

        $cart = Session::get(self::SESSION_KEY, []);
        $productId = $product->id;
        $maxStock = $product->tracksStock() ? (int) $product->stock : 999;

        $currentQty = isset($cart[$productId]) ? (int) $cart[$productId]['qty'] : 0;
        $newQty = $currentQty + $qty;

        if ($newQty > $maxStock) {
            $newQty = $maxStock;
        }

        $cart[$productId] = [
            'product_id'   => $product->id,
            'slug'         => $product->slug,
            'name'         => (string) ($product->translate('name', 'id') ?: $product->slug),
            'price'        => (float) $product->price,
            'qty'          => $newQty,
            'type'         => (string) $product->type,
            'thumbnail'    => $product->thumbnail ? Storage::url($product->thumbnail) : null,
            'tracks_stock' => $product->tracksStock(),
            'stock'        => $product->tracksStock() ? (int) $product->stock : null,
        ];

        Session::put(self::SESSION_KEY, $cart);
    }

    /**
     * Update quantity of a product in the cart.
     */
    public function update(int $productId, int $qty): void
    {
        $cart = Session::get(self::SESSION_KEY, []);

        if (! isset($cart[$productId])) {
            return;
        }

        if ($qty <= 0) {
            $this->remove($productId);
            return;
        }

        $max = isset($cart[$productId]['stock']) && $cart[$productId]['tracks_stock']
            ? (int) $cart[$productId]['stock']
            : 999;

        $cart[$productId]['qty'] = min($qty, $max);

        Session::put(self::SESSION_KEY, $cart);
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(int $productId): void
    {
        $cart = Session::get(self::SESSION_KEY, []);

        unset($cart[$productId]);

        Session::put(self::SESSION_KEY, $cart);
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
