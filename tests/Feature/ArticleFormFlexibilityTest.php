<?php

use App\Filament\ClientArea\Resources\ClientArticleResource\Pages\CreateClientArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Models\Article;
use App\Models\User;
use App\Support\ArticleContent;
use App\Support\ArticleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    foreach (['super_admin', 'admin', 'premium', 'regular_user'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }

    \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('client-area'));
});

/*
|--------------------------------------------------------------------------
| Meta description no longer hard-blocks at 160 characters
|--------------------------------------------------------------------------
| This is the exact "Jumlah karakter" error the user hit: a real meta
| description a little over the SEO-ideal 160 characters used to fail
| Filament's ->maxLength(160) validation outright. It's now a soft
| recommendation (helper text) backed by a generous 320-char ceiling.
*/
it('saves a client article with a meta description over 160 characters', function () {
    $client = User::factory()->create();
    $client->assignRole('regular_user');
    test()->actingAs($client);

    $longDescription = str_repeat('Panduan lengkap dan mendalam tentang topik ini. ', 5); // ~245 chars

    Livewire::test(CreateClientArticle::class)
        ->fillForm([
            'title'             => ['id' => 'Artikel Deskripsi Panjang'],
            'content'           => ['id' => 'Konten artikel di sini.'],
            'meta_description'  => ['id' => $longDescription],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $article = Article::whereJsonContains('title->id', 'Artikel Deskripsi Panjang')->first();

    expect($article)->not->toBeNull()
        ->and($article->getTranslation('meta_description', 'id', false))->toBe($longDescription);
});

/*
|--------------------------------------------------------------------------
| Backdating
|--------------------------------------------------------------------------
| "Publish Now" used to force published_at to now() unconditionally,
| discarding whatever date the author had picked. It now only defaults to
| now() when the field was left empty.
*/
it('keeps an explicitly backdated publish date when using Publish Now', function () {
    $backdated = now()->subMonths(3)->startOfMinute()->format('Y-m-d H:i:s');

    expect(CreateClientArticle::resolvePublishNowDate($backdated))->toBe($backdated);
});

it('defaults Publish Now to the current time when the field was left null or empty', function () {
    $fromNull  = \Illuminate\Support\Carbon::parse(CreateClientArticle::resolvePublishNowDate(null));
    $fromEmpty = \Illuminate\Support\Carbon::parse(CreateClientArticle::resolvePublishNowDate(''));

    expect($fromNull->diffInMinutes(now()))->toBeLessThan(2)
        ->and($fromEmpty->diffInMinutes(now()))->toBeLessThan(2); // blank() catches '' the same as null
});

/*
|--------------------------------------------------------------------------
| Content type (Blog / Article / News)
|--------------------------------------------------------------------------
*/
it('defaults a new article to type Article and persists a chosen type', function () {
    $client = User::factory()->create();
    $client->assignRole('regular_user');
    test()->actingAs($client);

    Livewire::test(CreateClientArticle::class)
        ->fillForm([
            'title'   => ['id' => 'Artikel Tanpa Tipe Eksplisit'],
            'content' => ['id' => 'Konten.'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $default = Article::whereJsonContains('title->id', 'Artikel Tanpa Tipe Eksplisit')->first();
    expect($default->type)->toBe(ArticleType::DEFAULT);

    Livewire::test(CreateClientArticle::class)
        ->fillForm([
            'title'   => ['id' => 'Berita Terbaru'],
            'content' => ['id' => 'Konten berita.'],
            'type'    => ArticleType::NEWS,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $news = Article::whereJsonContains('title->id', 'Berita Terbaru')->first();
    expect($news->type)->toBe(ArticleType::NEWS);
});

/*
|--------------------------------------------------------------------------
| Admin panel shares the same field behaviour
|--------------------------------------------------------------------------
| ArticleForm (admin) and ClientArticleResource now build their fields from
| the same App\Filament\Support\ArticleFields — this exercises the admin
| side once to confirm the new type/tags/meta_title fields don't break
| creation there too.
*/
it('creates an article from the admin panel with a single locale, a type, and a long meta description', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    test()->actingAs($admin);

    \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));

    // Draft on purpose: this test is about the form accepting a single
    // locale + the new type/meta_description fields without erroring, not
    // about re-exercising the publish gate (thumbnail/word-count), which
    // is already covered by ArticleContent tests elsewhere.
    Livewire::test(\App\Filament\Resources\Articles\Pages\CreateArticle::class)
        ->fillForm([
            'title.ms'             => 'Artikel Bahasa Melayu Dari Admin',
            'content.ms'           => str_repeat('kandungan ', 60),
            'meta_description.ms'  => str_repeat('Perihal produk ini secara terperinci. ', 5),
            'type'                 => ArticleType::BLOG,
            'status'               => 'Draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $article = Article::whereJsonContains('title->ms', 'Artikel Bahasa Melayu Dari Admin')->first();

    // The admin Meta section has one flat `slug` field (not slug.{locale}),
    // so Spatie Translatable stores it under whatever the app's current
    // locale is at submit time — not necessarily 'ms'. That's a pre-existing
    // characteristic of this form, not something this change affects; the
    // article stays reachable either way via ArticleController::show()'s
    // fallback across every locale key. Assert a slug exists at all, not
    // which specific key it landed under.
    expect($article)->not->toBeNull()
        ->and($article->type)->toBe(ArticleType::BLOG)
        ->and($article->getTranslation('meta_description', 'ms', false))->not->toBeEmpty()
        ->and($article->getTranslations('slug'))->not->toBe([]);
});

/*
|--------------------------------------------------------------------------
| Word count on live (unsaved) RichEditor state
|--------------------------------------------------------------------------
| Filament's RichEditor keeps its live form value as a TipTap JSON
| document, not HTML — only dehydrating to HTML on save. ArticleContent::
| wordCount() used to treat that shape as unusable and return 0, which
| made the publish gate report "0 kata" for articles that visibly had
| plenty of content, and could block a real save with status=Published.
*/
it('counts words in a live TipTap JSON document, not just saved HTML', function () {
    $doc = [
        'type' => 'doc',
        'content' => [
            ['type' => 'heading', 'attrs' => ['level' => 3], 'content' => [
                ['type' => 'text', 'text' => 'What is Lorem Ipsum?'],
            ]],
            ['type' => 'paragraph', 'content' => [
                ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'Lorem Ipsum'],
                ['type' => 'text', 'text' => ' is simply dummy text of the printing and typesetting industry.'],
            ]],
            ['type' => 'orderedList', 'content' => [
                ['type' => 'listItem', 'content' => [
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'first item']]],
                ]],
                ['type' => 'listItem', 'content' => [
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'second item']]],
                ]],
            ]],
        ],
    ];

    expect(ArticleContent::wordCount($doc))->toBeGreaterThanOrEqual(15);
});

it('lets bestWordCount clear the publish gate from a live TipTap document', function () {
    $words = collect(range(1, 60))->map(fn () => ['type' => 'text', 'text' => 'word'])->all();

    $doc = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => $words]]];

    $blockers = ArticleContent::publishBlockers([
        'content'   => ['en' => $doc],
        'thumbnail' => 'article-thumbnails/example.jpg',
    ]);

    expect($blockers)->toBe([]);
});

/*
|--------------------------------------------------------------------------
| Editing an existing article's slug
|--------------------------------------------------------------------------
| 'slug' is a Spatie-translatable JSON attribute bound to one flat
| TextInput (every locale shares the same value — see Article::boot()).
| Without formatStateUsing, Filament handed that field the raw per-locale
| array when populating the Edit form, which rendered as the literal text
| "[object Object]" instead of the slug.
*/
it('shows the slug as plain text, not the raw per-locale array, when editing an existing article', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    test()->actingAs($admin);

    \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));

    $article = Article::create([
        'title'   => ['en' => 'Existing Article', 'id' => 'Artikel Yang Ada'],
        'slug'    => ['en' => 'existing-article', 'id' => 'existing-article'],
        'content' => ['en' => str_repeat('word ', 60), 'id' => str_repeat('kata ', 60)],
        'status'  => 'Draft',
    ]);

    Livewire::test(EditArticle::class, ['record' => $article->getKey()])
        ->assertSet('data.slug', 'existing-article');
});

it('keeps the slug identical across every locale after re-saving an edited article', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    test()->actingAs($admin);

    \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));

    $article = Article::create([
        'title'   => ['en' => 'Renaming Candidate', 'id' => 'Kandidat Ganti Nama'],
        'slug'    => ['en' => 'renaming-candidate', 'id' => 'renaming-candidate'],
        'content' => ['en' => str_repeat('word ', 60), 'id' => str_repeat('kata ', 60)],
        'status'  => 'Draft',
    ]);

    Livewire::test(EditArticle::class, ['record' => $article->getKey()])
        ->fillForm(['slug' => 'renamed-slug'])
        ->call('save')
        ->assertHasNoFormErrors();

    $updated = $article->fresh();

    // toEqual (==), not toBe (===): key order isn't guaranteed to
    // round-trip through the JSON column the same way it was written —
    // only that every locale ended up with the same value.
    expect($updated->getTranslations('slug'))->toEqual([
        'en' => 'renamed-slug',
        'id' => 'renamed-slug',
        'ms' => 'renamed-slug',
        'ja' => 'renamed-slug',
    ]);
});
