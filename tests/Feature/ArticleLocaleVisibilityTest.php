<?php

use App\Models\Article;
use App\Support\ArticleContent;

/*
|--------------------------------------------------------------------------
| Per-locale article visibility
|--------------------------------------------------------------------------
| An article does not have to be translated into all four languages to go
| live. It only has to exist in one of them — and it should then only show
| up on the /blog of the locale(s) it actually has, never on another
| language's listing via translation fallback. See Article::scopeHasContentIn,
| ArticleContent::publishBlockers, and the Article::boot() slug fallback.
*/

it('shows an English-only article on the English /blog but not on the Indonesian one', function () {
    app()->setLocale('en');

    Article::factory()->published()->create([
        'title'   => ['en' => 'English Only Feature'],
        'slug'    => ['en' => 'english-only-feature'],
        'content' => ['en' => '<p>Written only in English.</p>'],
    ]);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSee('English Only Feature');

    app()->setLocale('id');

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertDontSee('English Only Feature')
        ->assertSee('Artikel Belum Tersedia');

    app()->setLocale('en');
});

it('shows an article written in two locales on both, and only those', function () {
    Article::factory()->published()->create([
        'title'   => ['id' => 'Artikel Dwi Bahasa', 'ms' => 'Artikel Dwi Bahasa MS'],
        'slug'    => ['id' => 'artikel-dwi-bahasa', 'ms' => 'artikel-dwi-bahasa'],
        'content' => ['id' => '<p>Konten bahasa Indonesia.</p>', 'ms' => '<p>Kandungan bahasa Melayu.</p>'],
    ]);

    app()->setLocale('id');
    $this->get(route('blog.index'))->assertOk()->assertSee('Artikel Dwi Bahasa');

    app()->setLocale('ms');
    $this->get(route('blog.index'))->assertOk()->assertSee('Artikel Dwi Bahasa MS');

    app()->setLocale('en');
    $this->get(route('blog.index'))->assertOk()->assertDontSee('Artikel Dwi Bahasa');

    app()->setLocale('en');
});

it('returns 404 for a published article viewed in a locale it was never written in', function () {
    Article::factory()->published()->create([
        'title'   => ['ja' => '日本語だけの記事'],
        'slug'    => ['ja' => 'nihongo-dake'],
        'content' => ['ja' => '<p>日本語のコンテンツです。</p>'],
    ]);

    app()->setLocale('ja');
    $this->get('/blog/nihongo-dake')->assertOk();

    app()->setLocale('en');
    $this->get('/blog/nihongo-dake')->assertNotFound();
});

it('does not recommend a related article that has no content in the current locale', function () {
    $main = Article::factory()->published()->create([
        'title'   => ['en' => 'Main Article'],
        'slug'    => ['en' => 'main-article-en-only'],
        'content' => ['en' => '<p>Main content.</p>'],
    ]);

    // Related, but only written in Japanese — must not appear as "related"
    // while browsing in English.
    Article::factory()->published()->create([
        'title'   => ['ja' => '関連記事'],
        'slug'    => ['ja' => 'kanren-kiji'],
        'content' => ['ja' => '<p>これは関連記事です。</p>'],
    ]);

    app()->setLocale('en');
    $this->get(route('blog.show', $main->slug))
        ->assertOk()
        ->assertDontSee('関連記事');
});

it('allows publishing with 50+ words in a single non-English, non-Indonesian locale', function () {
    $content = ['ms' => '<p>' . implode(' ', array_fill(0, 60, 'kata')) . '</p>'];

    expect(ArticleContent::publishBlockers([
        'content'   => $content,
        'thumbnail' => 'thumbnails/example.jpg',
    ]))->toBe([]);
});

it('still blocks publishing when no locale has enough words', function () {
    $content = ['ja' => '<p>短い。</p>'];

    $blockers = ArticleContent::publishBlockers([
        'content'   => $content,
        'thumbnail' => 'thumbnails/example.jpg',
    ]);

    expect($blockers)->not->toBe([])
        ->and($blockers[0])->toContain('EN/ID/MS/JA');
});

it('derives the slug from whichever locale was actually written when English and Indonesian are both blank', function () {
    $article = Article::create([
        'title'   => ['ms' => 'Panduan Lengkap Digitalisasi Perniagaan'],
        'content' => ['ms' => '<p>' . implode(' ', array_fill(0, 60, 'kandungan')) . '</p>'],
        'status'  => 'Draft',
    ]);

    expect($article->getTranslation('slug', 'ms', false))
        ->toBe('panduan-lengkap-digitalisasi-perniagaan')
        ->and($article->getTranslation('slug', 'en', false))
        ->toBe('panduan-lengkap-digitalisasi-perniagaan');
});

it('shows a locale-matching article on the homepage but not a different-locale-only one', function () {
    Article::factory()->published()->create([
        'title'   => ['en' => 'Homepage Teaser EN'],
        'slug'    => ['en' => 'homepage-teaser-en'],
        'content' => ['en' => '<p>English homepage teaser content.</p>'],
    ]);

    Article::factory()->published()->create([
        'title'   => ['ja' => 'ホームページのティーザー'],
        'slug'    => ['ja' => 'homepage-teaser-ja'],
        'content' => ['ja' => '<p>日本語のみのコンテンツです。</p>'],
    ]);

    app()->setLocale('en');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Homepage Teaser EN')
        ->assertDontSee('ホームページのティーザー');
});
