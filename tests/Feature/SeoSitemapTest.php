<?php

use App\Models\Article;

/*
|--------------------------------------------------------------------------
| Sitemap & SEO configuration
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    // Both are needed: Spatie's Sitemap builds absolute URLs through the
    // UrlGenerator, which resolved its root at boot and does not re-read
    // config('app.url') afterwards.
    config(['app.url' => 'https://rbeverything.com']);
    \Illuminate\Support\Facades\URL::forceRootUrl('https://rbeverything.com');
});

afterEach(function () {
    @unlink(public_path('sitemap-test-backup.xml'));
});

it('includes published articles in the sitemap using the right slug', function () {
    Article::factory()->published()->create([
        'title' => ['en' => 'Indexed Article', 'id' => 'Indexed Article'],
        'slug'  => ['en' => 'indexed-article', 'id' => 'artikel-terindeks'],
    ]);

    $this->artisan('app:generate-sitemap')->assertSuccessful();

    $xml = file_get_contents(public_path('sitemap.xml'));

    expect($xml)->toContain('https://rbeverything.com/blog/indexed-article')
        ->and($xml)->toContain('https://rbeverything.com/blog')
        ->and($xml)->not->toContain('localhost');
});

it('keeps drafts out of the sitemap', function () {
    Article::factory()->draft()->create([
        'slug' => ['en' => 'draft-not-indexed', 'id' => 'draft-not-indexed'],
    ]);

    $this->artisan('app:generate-sitemap')->assertSuccessful();

    expect(file_get_contents(public_path('sitemap.xml')))
        ->not->toContain('draft-not-indexed');
});

it('never emits a /blog/ entry for an article with no slug at all', function () {
    $article = Article::factory()->published()->create();

    \Illuminate\Support\Facades\DB::table('articles')
        ->where('id', $article->id)
        ->update(['slug' => json_encode([])]);

    $this->artisan('app:generate-sitemap')->assertSuccessful();

    expect(file_get_contents(public_path('sitemap.xml')))
        ->not->toContain('<loc>https://rbeverything.com/blog/</loc>');
});

it('regenerates the sitemap when a published article is saved', function () {
    config(['app.auto_sitemap' => true]);

    Article::factory()->published()->create([
        'slug' => ['en' => 'auto-refreshed', 'id' => 'auto-refreshed'],
    ]);

    // The refresh is deferred to app termination so it never slows a save.
    $this->app->terminate();

    expect(file_get_contents(public_path('sitemap.xml')))
        ->toContain('https://rbeverything.com/blog/auto-refreshed');
});

it('does not regenerate the sitemap when auto refresh is disabled', function () {
    $this->artisan('app:generate-sitemap');
    $before = filemtime(public_path('sitemap.xml'));

    config(['app.auto_sitemap' => false]);
    touch(public_path('sitemap.xml'), $before - 600);
    clearstatcache();

    Article::factory()->published()->create();
    $this->app->terminate();

    clearstatcache();
    expect(filemtime(public_path('sitemap.xml')))->toBe($before - 600);
});

it('flags a localhost APP_URL', function () {
    config(['app.url' => 'http://localhost']);
    \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');

    $this->artisan('app:check-seo-config')
        ->expectsOutputToContain('APP_URL is not a public URL')
        ->assertSuccessful();
});

it('flags APP_DEBUG in production', function () {
    config(['app.debug' => true, 'app.env' => 'production']);

    $this->artisan('app:check-seo-config')
        ->expectsOutputToContain('APP_DEBUG is true in production')
        ->assertSuccessful();
});

it('flags a stale sitemap', function () {
    $this->artisan('app:generate-sitemap');
    touch(public_path('sitemap.xml'), time() - (30 * 86400));
    clearstatcache();

    $this->artisan('app:check-seo-config')
        ->expectsOutputToContain('older than 7 days')
        ->assertSuccessful();
});

it('blocks the real admin path in robots.txt', function () {
    $robots = file_get_contents(public_path('robots.txt'));

    expect($robots)->toContain('Disallow: /rbdashboard/')
        ->and($robots)->toContain('Disallow: /client-area/')
        ->and($robots)->toContain('Disallow: /system/')
        // /admin/ was never a real path on this site.
        ->and($robots)->not->toContain('Disallow: /admin/');
});
