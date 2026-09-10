<?php

use App\Models\Product;
use App\Models\User;
use App\Support\ProductType;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    foreach (['super_admin', 'admin', 'regular_user'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
});

/*
|--------------------------------------------------------------------------
| Listing
|--------------------------------------------------------------------------
*/

it('serves the catalogue listing', function () {
    get(route('products.index'))
        ->assertOk()
        ->assertSee('Produk &amp; Layanan', false);
});

it('shows active products and hides inactive ones', function () {
    Product::factory()->create(['name' => ['id' => 'Router Wi-Fi Terpasang']]);
    Product::factory()->inactive()->create(['name' => ['id' => 'Produk Disembunyikan']]);

    get(route('products.index'))
        ->assertOk()
        ->assertSee('Router Wi-Fi Terpasang')
        ->assertDontSee('Produk Disembunyikan');
});

it('filters the listing by type', function () {
    Product::factory()->barang()->create(['name' => ['id' => 'Kabel LAN Cat6']]);
    Product::factory()->jasa()->create(['name' => ['id' => 'Audit Keamanan Website']]);

    get(route('products.index', ['type' => ProductType::BARANG]))
        ->assertOk()
        ->assertSee('Kabel LAN Cat6')
        ->assertDontSee('Audit Keamanan Website');

    get(route('products.index', ['type' => ProductType::JASA]))
        ->assertOk()
        ->assertSee('Audit Keamanan Website')
        ->assertDontSee('Kabel LAN Cat6');
});

it('ignores a nonsense type filter instead of failing', function () {
    Product::factory()->barang()->create(['name' => ['id' => 'Kabel LAN Cat6']]);

    // A hand-edited query string should show everything, not a 500.
    get(route('products.index', ['type' => 'tidak-ada']))
        ->assertOk()
        ->assertSee('Kabel LAN Cat6');
});

it('shows a designed empty state when nothing is published yet', function () {
    get(route('products.index'))
        ->assertOk()
        ->assertSee('Katalog sedang disiapkan');
});

it('shows a category-specific empty state when only the other type exists', function () {
    Product::factory()->jasa()->create();

    get(route('products.index', ['type' => ProductType::BARANG]))
        ->assertOk()
        ->assertSee('Belum ada Barang yang tayang');
});

it('renders "Hubungi Kami" instead of a price when there is none', function () {
    Product::factory()->withoutPrice()->create(['name' => ['id' => 'Rakit PC Custom']]);

    get(route('products.index'))
        ->assertOk()
        ->assertSee('Hubungi Kami');
});

/*
|--------------------------------------------------------------------------
| Detail page
|--------------------------------------------------------------------------
*/

it('serves a product detail page', function () {
    $product = Product::factory()->create([
        'name'  => ['id' => 'Paket Router Wi-Fi 6'],
        'price' => 450000,
    ]);

    get(route('products.show', $product->slug))
        ->assertOk()
        ->assertSee('Paket Router Wi-Fi 6')
        ->assertSee('Rp 450.000');
});

it('404s on an unknown slug', function () {
    get(route('products.show', 'slug-yang-tidak-ada'))->assertNotFound();
});

it('404s on a hidden product for the public', function () {
    $hidden = Product::factory()->inactive()->create();

    get(route('products.show', $hidden->slug))->assertNotFound();
});

it('lets an admin preview a hidden product', function () {
    $hidden = Product::factory()->inactive()->create(['name' => ['id' => 'Draf Produk Baru']]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    actingAs($admin)
        ->get(route('products.show', $hidden->slug))
        ->assertOk()
        ->assertSee('Draf Produk Baru')
        // A page only an admin can see must never be indexed.
        ->assertSee('noindex, nofollow', false);
});

it('still 404s a hidden product for a signed-in client-area user', function () {
    $hidden = Product::factory()->inactive()->create();

    $client = User::factory()->create();
    $client->assignRole('regular_user');

    actingAs($client)
        ->get(route('products.show', $hidden->slug))
        ->assertNotFound();
});

it('renders the sanitised description without escaping its markup', function () {
    $product = Product::factory()->create([
        'description' => ['id' => '<p>Deskripsi <strong>lengkap</strong> produk.</p><script>alert(1)</script>'],
    ]);

    $response = get(route('products.show', $product->slug))->assertOk();

    $response->assertSee('<strong>lengkap</strong>', false)
        ->assertDontSee('<script>alert(1)</script>', false);
});

/*
|--------------------------------------------------------------------------
| Languages
|--------------------------------------------------------------------------
*/

it('renders the catalogue in whichever language the visitor picked', function () {
    Product::factory()->create([
        'name' => [
            'id' => 'Audit Keamanan Website',
            'en' => 'Website Security Audit',
        ],
    ]);

    foreach ([
        'en' => 'Website Security Audit',
        'id' => 'Audit Keamanan Website',
    ] as $locale => $expected) {
        $this->withSession(['locale' => $locale])
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee($expected, false);
    }
});

it('falls back instead of going blank in a language the product lacks', function () {
    $product = Product::factory()->indonesianOnly()->create([
        'name' => ['id' => 'Kabel LAN Cat6'],
    ]);

    // Articles hide themselves in an untranslated locale; a product must not.
    $this->withSession(['locale' => 'en'])
        ->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertSee('Kabel LAN Cat6');
});

it('offers only the enabled languages on the catalogue pages', function () {
    $product = Product::factory()->create();

    foreach ([route('products.index'), route('products.show', $product->slug)] as $url) {
        $response = get($url)->assertOk();

        foreach (['id', 'en'] as $locale) {
            $response->assertSee(route('lang.switch', $locale), false);
        }

        // Malay and Japanese are switched off — no button may offer them.
        foreach (['ms', 'ja'] as $locale) {
            $response->assertDontSee(route('lang.switch', $locale), false);
        }
    }
});

it('actually switches the rendered language through the switcher route', function () {
    Product::factory()->create([
        'name' => ['id' => 'Audit Keamanan Website', 'en' => 'Website Security Audit'],
    ]);

    $this->get(route('lang.switch', 'en'))->assertRedirect();

    get(route('products.index'))
        ->assertOk()
        ->assertSee('Website Security Audit');
});

/*
|--------------------------------------------------------------------------
| Navigation & sitemap
|--------------------------------------------------------------------------
*/

it('links the main navigation to the catalogue', function () {
    get(route('home'))
        ->assertOk()
        ->assertSee(route('products.index', ['type' => 'barang']), false)
        ->assertSee(route('products.index', ['type' => 'jasa']), false);
});

it('lists active products in the generated sitemap', function () {
    // Same pinning as SeoSitemapTest: Spatie's Sitemap resolves absolute URLs
    // through the UrlGenerator, which read its root at boot.
    config(['app.url' => 'https://rbeverything.com']);
    \Illuminate\Support\Facades\URL::forceRootUrl('https://rbeverything.com');

    $visible = Product::factory()->create();
    $hidden  = Product::factory()->inactive()->create();

    $this->artisan('app:generate-sitemap')->assertSuccessful();

    $sitemap = file_get_contents(public_path('sitemap.xml'));

    expect($sitemap)->toContain('https://rbeverything.com/produk-layanan')
        ->and($sitemap)->toContain("https://rbeverything.com/produk-layanan/{$visible->slug}")
        ->and($sitemap)->not->toContain("/produk-layanan/{$hidden->slug}");
});
