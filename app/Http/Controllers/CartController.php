<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cart
    ) {}

    public function index(): View
    {
        return view('cart.index', [
            'items'    => $this->cart->items(),
            'count'    => $this->cart->count(),
            'subtotal' => $this->cart->formattedSubtotal(),
            'subtotalNumeric' => $this->cart->subtotal(),
            'isEmpty'  => $this->cart->isEmpty(),
            'hasPhysicalItems' => $this->cart->hasPhysicalItems(),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        if (! $product->is_active) {
            abort(404);
        }

        if (! $product->hasPrice()) {
            return back()->with('order_error', 'Produk ini belum memiliki harga tetap.');
        }

        if (! $product->isInStock()) {
            return back()->with('order_error', 'Stok produk ini sedang habis.');
        }

        $qty = max(1, (int) $request->input('qty', 1));
        $this->cart->add($product, $qty);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke keranjang!',
                'count'   => $this->cart->count(),
                'subtotal' => $this->cart->formattedSubtotal(),
            ]);
        }

        if ($request->input('buy_now')) {
            return redirect()->route('checkout.index');
        }

        return redirect()->route('cart.index')
            ->with('cart_success', 'Produk berhasil ditambahkan ke keranjang belanja.');
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $productId = (int) $request->input('product_id');
        $qty = (int) $request->input('qty', 1);

        $this->cart->update($productId, $qty);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count'   => $this->cart->count(),
                'subtotal' => $this->cart->formattedSubtotal(),
            ]);
        }

        return redirect()->route('cart.index');
    }

    public function remove(int $productId): RedirectResponse|JsonResponse
    {
        $this->cart->remove($productId);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'count'   => $this->cart->count(),
                'subtotal' => $this->cart->formattedSubtotal(),
            ]);
        }

        return redirect()->route('cart.index')
            ->with('cart_info', 'Item dihapus dari keranjang.');
    }

    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        return redirect()->route('cart.index')
            ->with('cart_info', 'Keranjang belanja telah dikosongkan.');
    }
}
