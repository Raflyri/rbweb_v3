<?php

use App\Models\Product;
use App\Support\ProductType;

/*
|--------------------------------------------------------------------------
| Slug generation
|--------------------------------------------------------------------------
*/

it('generates a slug from the Indonesian name', function () {
    $product = Product::create([
        'name' => ['id' => 'Paket Router Wi-Fi 6', 'en' => 'Wi-Fi 6 Router Bundle'],
        'type' => ProductType::BARANG,
    ]);

    expect($product->slug)->toBe('paket-router-wi-fi-6');
});

it('falls back to the English name when there is no Indonesian one', function () {
    $product = Product::create([
        'name' => ['en' => 'Server Migration Assistance'],
        'type' => ProductType::JASA,
    ]);

    expect($product->slug)->toBe('server-migration-assistance');
});

it('keeps slugs unique across products', function () {
    $first  = Product::create(['name' => ['id' => 'Audit Keamanan'], 'type' => ProductType::JASA]);
    $second = Product::create(['name' => ['id' => 'Audit Keamanan'], 'type' => ProductType::JASA]);

    expect($first->slug)->toBe('audit-keamanan')
        ->and($second->slug)->not->toBe($first->slug)
        ->and($second->slug)->toStartWith('audit-keamanan');
});

it('does not move an existing permalink when the product is renamed', function () {
    $product = Product::create(['name' => ['id' => 'Nama Lama'], 'type' => ProductType::BARANG]);

    $product->update(['name' => ['id' => 'Nama Baru Yang Berbeda']]);

    // Renaming must never break links that are already out in the world.
    expect($product->fresh()->slug)->toBe('nama-lama');
});

it('accepts a slug typed by hand instead of generating one', function () {
    $product = Product::create([
        'slug' => 'slug-pilihan-admin',
        'name' => ['id' => 'Nama Produk'],
        'type' => ProductType::BARANG,
    ]);

    expect($product->slug)->toBe('slug-pilihan-admin');
});

/*
|--------------------------------------------------------------------------
| Price formatting
|--------------------------------------------------------------------------
*/

it('formats a price in rupiah', function () {
    $product = Product::factory()->create(['price' => 1500000]);

    expect($product->formattedPrice())->toBe('Rp 1.500.000')
        ->and($product->hasPrice())->toBeTrue();
});

it('shows cents only when they are not zero', function () {
    $product = Product::factory()->create(['price' => 1500000.50]);

    expect($product->formattedPrice())->toBe('Rp 1.500.000,50');
});

it('returns no formatted price at all when the price is empty', function () {
    $product = Product::factory()->withoutPrice()->create();

    // null is the signal every view uses to render "Hubungi Kami" instead.
    expect($product->formattedPrice())->toBeNull()
        ->and($product->hasPrice())->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Scopes
|--------------------------------------------------------------------------
*/

it('filters products by visibility and type', function () {
    $activeBarang = Product::factory()->barang()->create();
    $activeJasa   = Product::factory()->jasa()->create();
    $hidden       = Product::factory()->barang()->inactive()->create();

    expect(Product::active()->pluck('id')->all())
        ->toContain($activeBarang->id, $activeJasa->id)
        ->not->toContain($hidden->id);

    expect(Product::barang()->pluck('id')->all())->toContain($activeBarang->id)
        ->not->toContain($activeJasa->id);

    expect(Product::jasa()->pluck('id')->all())->toContain($activeJasa->id)
        ->not->toContain($activeBarang->id);
});

it('puts featured products first in the display order', function () {
    $plain    = Product::factory()->create(['sort_order' => 1]);
    $featured = Product::factory()->featured()->create(['sort_order' => 99]);

    expect(Product::inDisplayOrder()->first()->id)->toBe($featured->id)
        ->and(Product::inDisplayOrder()->get()->last()->id)->toBe($plain->id);
});

/*
|--------------------------------------------------------------------------
| Translation fallback
|--------------------------------------------------------------------------
*/

it('serves the Indonesian copy to visitors browsing in an untranslated locale', function () {
    $product = Product::factory()->indonesianOnly()->create([
        'name' => ['id' => 'Kabel LAN Cat6'],
    ]);

    // Malay and Japanese are supported site-wide but products are only written
    // in id/en, so those visitors get Indonesian rather than an empty page.
    expect($product->translate('name', 'ms'))->toBe('Kabel LAN Cat6')
        ->and($product->translate('name', 'ja'))->toBe('Kabel LAN Cat6')
        ->and($product->translate('name', 'en'))->toBe('Kabel LAN Cat6');
});

it('prefers the requested locale when it exists', function () {
    $product = Product::factory()->create([
        'name' => ['id' => 'Audit Keamanan Website', 'en' => 'Website Security Audit'],
    ]);

    expect($product->translate('name', 'en'))->toBe('Website Security Audit')
        ->and($product->translate('name', 'id'))->toBe('Audit Keamanan Website');
});

/*
|--------------------------------------------------------------------------
| Sanitisation
|--------------------------------------------------------------------------
*/

it('strips dangerous markup from the description before storing it', function () {
    $product = Product::factory()->create([
        'description' => [
            'id' => '<p>Aman</p><script>alert("xss")</script><p onclick="steal()">Klik</p>',
        ],
    ]);

    $stored = $product->fresh()->getTranslation('description', 'id');

    expect($stored)->not->toContain('<script>')
        ->and($stored)->not->toContain('onclick')
        ->and($stored)->toContain('Aman');
});

it('drops locales that were left blank in the editor', function () {
    $product = Product::factory()->create([
        'name'        => ['id' => 'Produk Uji', 'en' => 'Test Product'],
        'description' => ['id' => '<p>Ada isinya</p>', 'en' => '<p></p>'],
    ]);

    // An untouched RichEditor dehydrates to "<p></p>" — present but empty.
    // Left in the JSON it would win over the locale that has real copy.
    expect($product->fresh()->getTranslations('description'))->not->toHaveKey('en');
});

it('backfills the meta description from the short description', function () {
    $product = Product::factory()->create([
        'short_description' => ['id' => 'Router Wi-Fi 6 lengkap dengan pemasangan di lokasi.'],
        'meta_description'  => [],
    ]);

    expect($product->fresh()->getTranslation('meta_description', 'id'))
        ->toBe('Router Wi-Fi 6 lengkap dengan pemasangan di lokasi.');
});

/*
|--------------------------------------------------------------------------
| Stock
|--------------------------------------------------------------------------
*/

it('treats services and untracked goods as always available', function () {
    $service   = Product::factory()->jasa()->create();
    $untracked = Product::factory()->barang()->create(['stock' => null]);

    expect($service->tracksStock())->toBeFalse()
        ->and($service->isInStock())->toBeTrue()
        ->and($untracked->isInStock())->toBeTrue();
});

it('reports tracked goods with zero stock as unavailable', function () {
    $soldOut = Product::factory()->outOfStock()->create();

    expect($soldOut->tracksStock())->toBeTrue()
        ->and($soldOut->isInStock())->toBeFalse();
});
