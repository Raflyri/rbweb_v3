<?php

use App\Filament\ClientArea\Resources\ClientArticleResource\Pages\CreateClientArticle;
use App\Models\Article;
use App\Models\User;
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
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    test()->actingAs($admin);

    $backdated = now()->subMonths(3)->startOfMinute();

    Livewire::test(CreateClientArticle::class)
        ->fillForm([
            'title'        => ['en' => 'Backdated Announcement'],
            'content'      => ['en' => 'Content written well after the fact.'],
            'published_at' => $backdated->format('Y-m-d H:i:s'),
        ])
        ->callAction('publishNow');

    $article = Article::whereJsonContains('title->en', 'Backdated Announcement')->first();

    expect($article)->not->toBeNull()
        ->and($article->status)->toBe('Published')
        ->and($article->published_at->format('Y-m-d H:i'))->toBe($backdated->format('Y-m-d H:i'));
});

it('defaults Publish Now to the current time when no date was picked', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    test()->actingAs($admin);

    Livewire::test(CreateClientArticle::class)
        ->fillForm([
            'title'   => ['en' => 'Published Right Now'],
            'content' => ['en' => 'Content.'],
        ])
        ->callAction('publishNow');

    $article = Article::whereJsonContains('title->en', 'Published Right Now')->first();

    expect($article)->not->toBeNull()
        ->and($article->status)->toBe('Published')
        ->and($article->published_at->diffInMinutes(now()))->toBeLessThan(2);
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

    expect($article)->not->toBeNull()
        ->and($article->type)->toBe(ArticleType::BLOG)
        ->and($article->getTranslation('meta_description', 'ms', false))->not->toBeEmpty()
        ->and($article->getTranslation('slug', 'ms', false))->not->toBeEmpty();
});
