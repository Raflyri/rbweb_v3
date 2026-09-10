<?php

use App\Models\Product;
use App\Support\ArticleLocale;

use function Pest\Laravel\get;

/*
|--------------------------------------------------------------------------
| Which languages the site currently offers
|--------------------------------------------------------------------------
| Malay and Japanese are switched off for now. "Off" has to mean three
| separate things, and each is tested here: nobody is offered them, nobody can
| reach them by typing a URL, and nothing already written in them is lost.
*/

it('offers Indonesian and English, and nothing else', function () {
    expect(ArticleLocale::ENABLED)->toBe(['id', 'en'])
        ->and(ArticleLocale::isEnabled('id'))->toBeTrue()
        ->and(ArticleLocale::isEnabled('en'))->toBeTrue()
        ->and(ArticleLocale::isEnabled('ms'))->toBeFalse()
        ->and(ArticleLocale::isEnabled('ja'))->toBeFalse();
});

it('still recognises the switched-off locales as valid storage keys', function () {
    // The distinction that makes this reversible: 'ms' remains a legal key to
    // read and write, it is simply never offered to anyone.
    expect(ArticleLocale::isSupported('ms'))->toBeTrue()
        ->and(ArticleLocale::isSupported('ja'))->toBeTrue()
        ->and(ArticleLocale::normalize('my'))->toBe('ms')
        ->and(ArticleLocale::lookupKeys())->toContain('ms', 'ja');
});

it('defaults to Indonesian', function () {
    expect(config('app.locale'))->toBe('id')
        ->and(ArticleLocale::FALLBACK)->toBe('id')
        // Anything unmappable lands on Indonesian rather than English.
        ->and(ArticleLocale::normalize('fr'))->toBe('id')
        ->and(ArticleLocale::normalize(null))->toBe('id');
});

it('renders pages in Indonesian for a visitor who has chosen nothing', function () {
    get(route('products.index'))
        ->assertOk()
        ->assertSee('lang="id"', false);
});

it('refuses to switch into a language that is turned off', function () {
    $this->from(route('products.index'))
        ->get(route('lang.switch', 'ms'))
        ->assertRedirect();

    // The request is accepted and redirected — it just changes nothing.
    expect(session('locale'))->toBeNull();
});

it('accepts a switch into an enabled language', function () {
    $this->from(route('products.index'))
        ->get(route('lang.switch', 'en'))
        ->assertRedirect();

    expect(session('locale'))->toBe('en');
});

it('rescues a visitor stranded in a language that was switched off', function () {
    Product::factory()->create(['name' => ['id' => 'Kabel LAN Cat6']]);

    // Someone who picked Malay before it was disabled still has it in their
    // session. Without the clamp in SetLocale they would be stuck in a locale
    // the switcher no longer has a button to leave.
    $this->withSession(['locale' => 'ms'])
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee('lang="id"', false)
        ->assertSee('Kabel LAN Cat6')
        // The stale choice is dropped rather than re-applied on every request.
        ->assertSessionMissing('locale');
});

it('keeps the switcher down to the enabled languages on the homepage', function () {
    $response = get(route('home'))->assertOk();

    $response->assertSee('data-lang="id"', false)
        ->assertSee('data-lang="en"', false)
        ->assertDontSee('data-lang="ms"', false)
        ->assertDontSee('data-lang="ja"', false);
});
