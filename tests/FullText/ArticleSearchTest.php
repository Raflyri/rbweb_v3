<?php

use App\Models\Article;
use App\Support\ArticleSearch;

/*
|--------------------------------------------------------------------------
| /blog?search=…
|--------------------------------------------------------------------------
| The FULLTEXT index has existed since April but nothing used it. These tests
| pin down the behaviour it replaces LIKE with, including the cases that must
| still fall back to LIKE.
*/

function seedSearchable(): void
{
    Article::factory()->published()->create([
        'title'   => ['en' => 'Laravel Queue Internals', 'id' => 'Laravel Queue Internals'],
        'slug'    => ['en' => 'laravel-queue-internals', 'id' => 'laravel-queue-internals'],
        'content' => ['en' => '<p>A long look at how workers reserve jobs and handle failures.</p>',
                      'id' => '<p>Pembahasan mendalam soal worker antrian.</p>'],
    ]);

    Article::factory()->published()->create([
        'title'   => ['en' => 'Python for Data Science', 'id' => 'Python untuk Sains Data'],
        'slug'    => ['en' => 'python-data-science', 'id' => 'python-data-science'],
        'content' => ['en' => '<p>Notebooks, dataframes and plotting basics.</p>',
                      'id' => '<p>Dasar notebook dan dataframe.</p>'],
    ]);
}

it('finds an article by a word in the title', function () {
    seedSearchable();

    $this->get(route('blog.index', ['search' => 'Laravel']))
        ->assertOk()
        ->assertSee('Laravel Queue Internals')
        ->assertDontSee('Python for Data Science');
});

it('finds an article by a word in the body', function () {
    seedSearchable();

    $this->get(route('blog.index', ['search' => 'dataframes']))
        ->assertOk()
        ->assertSee('Python for Data Science')
        ->assertDontSee('Laravel Queue Internals');
});

it('matches on a prefix the way the old LIKE search did', function () {
    seedSearchable();

    $this->get(route('blog.index', ['search' => 'Larav']))
        ->assertOk()
        ->assertSee('Laravel Queue Internals');
});

it('returns an empty result set, not an error, for a word that matches nothing', function () {
    seedSearchable();

    $this->get(route('blog.index', ['search' => 'kubernetes']))
        ->assertOk()
        ->assertSee('No articles found')
        ->assertDontSee('Laravel Queue Internals');
});

it('survives boolean-mode operators typed by the user', function () {
    seedSearchable();

    foreach (['+Laravel', '-Laravel', 'Laravel (', '~Laravel*', '"Laravel'] as $term) {
        $this->get(route('blog.index', ['search' => $term]))->assertOk();
    }
});

it('falls back to LIKE for a term too short to be indexed', function () {
    Article::factory()->published()->create([
        'title' => ['en' => 'Go Concurrency', 'id' => 'Go Concurrency'],
        'slug'  => ['en' => 'go-concurrency', 'id' => 'go-concurrency'],
    ]);

    expect(ArticleSearch::booleanTerms('Go'))->toBeNull();

    $this->get(route('blog.index', ['search' => 'Go']))
        ->assertOk()
        ->assertSee('Go Concurrency');
});

it('finds a Japanese article via the LIKE fallback', function () {
    app()->setLocale('ja');

    Article::factory()->published()->create([
        'title'   => ['ja' => '日本語の記事', 'en' => 'Japanese Article'],
        'slug'    => ['ja' => 'nihongo-kiji', 'en' => 'nihongo-kiji'],
        'content' => ['ja' => '<p>これはテスト記事です。</p>'],
    ]);

    $this->get(route('blog.index', ['search' => '日本語']))
        ->assertOk()
        ->assertSee('日本語の記事');
});

it('does not leak drafts into search results', function () {
    Article::factory()->draft()->create([
        'title' => ['en' => 'Laravel Secret Draft', 'id' => 'Laravel Secret Draft'],
        'slug'  => ['en' => 'laravel-secret-draft', 'id' => 'laravel-secret-draft'],
    ]);

    $this->get(route('blog.index', ['search' => 'Laravel']))
        ->assertOk()
        ->assertDontSee('Laravel Secret Draft');
});

it('treats a LIKE wildcard in the search term as a literal', function () {
    Article::factory()->published()->create([
        'title' => ['en' => 'Discount Guide', 'id' => 'Discount Guide'],
        'slug'  => ['en' => 'discount-guide', 'id' => 'discount-guide'],
    ]);

    // '%' is short enough to take the LIKE path; unescaped it would match all.
    $this->get(route('blog.index', ['search' => '%']))
        ->assertOk()
        ->assertDontSee('Discount Guide');
});
