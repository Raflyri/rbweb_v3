<?php

use App\Models\Article;
use App\Support\ArticleLocale;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Locale key consistency
|--------------------------------------------------------------------------
| Articles must round-trip through both panels without losing a translation,
| and legacy keys already sitting in the database must migrate cleanly.
*/

it('normalises every locale variant onto a supported key', function () {
    expect(ArticleLocale::normalize('en_US'))->toBe('en')
        ->and(ArticleLocale::normalize('en-GB'))->toBe('en')
        ->and(ArticleLocale::normalize('my'))->toBe('ms')
        ->and(ArticleLocale::normalize('jp'))->toBe('ja')
        ->and(ArticleLocale::normalize('ja'))->toBe('ja')
        ->and(ArticleLocale::normalize(null))->toBe('en');
});

it('keeps every translation when an admin-created article is edited by a client', function () {
    // Admin form writes en/id/ms/ja.
    $article = Article::factory()->create([
        'title'   => ['en' => 'Admin EN', 'id' => 'Admin ID', 'ms' => 'Admin MS', 'ja' => 'Admin JA'],
        'content' => ['en' => '<p>EN body</p>', 'id' => '<p>ID body</p>', 'ms' => '<p>MS body</p>', 'ja' => '<p>JA body</p>'],
        'slug'    => ['en' => 'admin-article', 'id' => 'admin-article'],
    ]);

    // Client editor writes back the same four keys.
    $article->setTranslation('title', 'id', 'Client edited ID');
    $article->save();

    $fresh = $article->fresh();

    expect($fresh->getTranslation('title', 'id', false))->toBe('Client edited ID')
        ->and($fresh->getTranslation('title', 'en', false))->toBe('Admin EN')
        ->and($fresh->getTranslation('title', 'ms', false))->toBe('Admin MS')
        ->and($fresh->getTranslation('title', 'ja', false))->toBe('Admin JA');
});

it('exposes the same four locales to both editors', function () {
    expect(ArticleLocale::editorLocales())->toEqualCanonicalizing(ArticleLocale::SUPPORTED)
        ->and(ArticleLocale::SUPPORTED)->toBe(['en', 'id', 'ms', 'ja']);
});

it('migrates legacy my/jp/en_US keys onto canonical keys', function () {
    $article = Article::factory()->create();

    DB::table('articles')->where('id', $article->id)->update([
        'title'   => json_encode(['id' => 'Judul ID', 'my' => 'Tajuk MS', 'jp' => 'タイトル', 'en_US' => 'Title EN']),
        'content' => json_encode(['my' => '<p>Kandungan</p>', 'jp' => '<p>本文</p>']),
    ]);

    $this->artisan('articles:fix-locale-keys', ['--force' => true])->assertSuccessful();

    $title   = json_decode(DB::table('articles')->where('id', $article->id)->value('title'), true);
    $content = json_decode(DB::table('articles')->where('id', $article->id)->value('content'), true);

    expect(array_keys($title))->toEqualCanonicalizing(['id', 'ms', 'ja', 'en'])
        ->and($title['ms'])->toBe('Tajuk MS')
        ->and($title['ja'])->toBe('タイトル')
        ->and($title['en'])->toBe('Title EN')
        ->and(array_keys($content))->toEqualCanonicalizing(['ms', 'ja']);
});

it('drops empty legacy keys without inventing a translation', function () {
    $article = Article::factory()->create();

    DB::table('articles')->where('id', $article->id)->update([
        'title' => json_encode(['id' => 'Judul', 'jp' => null]),
    ]);

    $this->artisan('articles:fix-locale-keys', ['--force' => true])->assertSuccessful();

    $title = json_decode(DB::table('articles')->where('id', $article->id)->value('title'), true);

    expect(array_keys($title))->toBe(['id'])
        ->and($title)->not->toHaveKey('ja');
});

it('never overwrites an existing translation and reports the conflict instead', function () {
    $article = Article::factory()->create();

    DB::table('articles')->where('id', $article->id)->update([
        'title' => json_encode(['ms' => 'Sudah ada', 'my' => 'Versi lama berbeda']),
    ]);

    $this->artisan('articles:fix-locale-keys', ['--force' => true])
        ->expectsOutputToContain('resolve by hand')
        ->assertSuccessful();

    $title = json_decode(DB::table('articles')->where('id', $article->id)->value('title'), true);

    expect($title['ms'])->toBe('Sudah ada')
        ->and($title['my'])->toBe('Versi lama berbeda');
});

it('changes nothing in dry-run mode', function () {
    $article = Article::factory()->create();

    DB::table('articles')->where('id', $article->id)->update([
        'title' => json_encode(['jp' => 'タイトル']),
    ]);

    $this->artisan('articles:fix-locale-keys', ['--dry-run' => true])->assertSuccessful();

    $title = json_decode(DB::table('articles')->where('id', $article->id)->value('title'), true);

    expect(array_keys($title))->toBe(['jp']);
});

it('resolves a slug that is only stored under a legacy locale key', function () {
    $article = Article::factory()->published()->create();

    DB::table('articles')->where('id', $article->id)->update([
        'slug' => json_encode(['jp' => 'artikel-lama']),
    ]);

    $this->get('/blog/artikel-lama')->assertOk();
});
