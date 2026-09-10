<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Policies\ProductPolicy;
use App\Settings\GeneralSettings;
use App\Support\ArticleLocale;
use App\Support\ProductType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * The public catalogue at /produk-layanan, optionally filtered by type.
     *
     * Unlike /blog, nothing here is hidden for being written in the "wrong"
     * language: products are only ever written in id/en and Product::translate()
     * serves the closest available copy, so a Malay or Japanese visitor sees
     * the full catalogue rather than an empty page.
     */
    public function index(Request $request): View
    {
        $locale = ArticleLocale::current();

        // Anything that is not a real type is treated as "no filter" rather
        // than an error — a hand-edited query string should not 500.
        $type = $request->query('type');
        $type = ProductType::isValid($type) ? $type : null;

        $products = Product::query()
            ->active()
            ->when($type, fn ($query) => $query->ofType($type))
            ->inDisplayOrder()
            ->paginate(12)
            ->withQueryString();

        // Counts drive the numbers on the filter tabs, and they must reflect
        // the whole catalogue rather than the page currently being shown.
        $counts = [
            'all'                => Product::active()->count(),
            ProductType::BARANG  => Product::active()->barang()->count(),
            ProductType::JASA    => Product::active()->jasa()->count(),
        ];

        return view('products.index', compact('products', 'type', 'counts', 'locale'));
    }

    /**
     * A single product page.
     *
     * A hidden product 404s for the public but stays reachable for an admin,
     * so a listing can be checked in place before it is switched on.
     */
    public function show(string $slug): View
    {
        $locale = ArticleLocale::current();

        $product = Product::where('slug', $slug)->firstOrFail();

        if (! $product->is_active && ! ProductPolicy::userIsManager(auth()->user())) {
            abort(404);
        }

        $related = Product::active()
            ->where('id', '!=', $product->id)
            ->ofType($product->type)
            ->inDisplayOrder()
            ->take(3)
            ->get();

        $contact = $this->contactLinks();

        return view('products.show', compact('product', 'related', 'locale', 'contact'));
    }

    /**
     * Where "Hubungi Kami" actually goes.
     *
     * Reads the same GeneralSettings the homepage footer uses so there is one
     * phone number and one address to keep current, not three. Settings live
     * in the database, so a missing row falls back to config rather than
     * taking the whole product page down with it.
     *
     * @return array{email: string, whatsapp: ?string}
     */
    protected function contactLinks(): array
    {
        $email    = (string) config('mail.from.address', 'hello@rbeverything.com');
        $whatsapp = null;

        try {
            $settings = app(GeneralSettings::class);

            $email = $settings->contact_email ?: $email;

            if ($settings->whatsapp_number) {
                $whatsapp = 'https://wa.me/' . preg_replace('/\D/', '', $settings->whatsapp_number);
            }
        } catch (\Throwable $e) {
            // Settings not seeded (fresh install, test database): the config
            // fallback above is still a working mailto: target.
        }

        return ['email' => $email, 'whatsapp' => $whatsapp];
    }
}
