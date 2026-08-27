<?php

use App\Models\Article;

/*
|--------------------------------------------------------------------------
| articles:cleanup-test-data
|--------------------------------------------------------------------------
| The command must catch every junk signature (URL title, near-empty body,
| Published with no date) while never touching a real article.
*/

it('flags an article whose title is a URL', function () {
    $junk = Article::factory()->published()->urlTitle()->create();

    $this->artisan('articles:cleanup-test-data', ['--mode' => 'draft', '--force' => true])
        ->assertSuccessful();

    expect($junk->fresh()->status)->toBe('Draft');
});

it('flags an article with almost no content', function () {
    $junk = Article::factory()->published()->emptyContent()->create();

    $this->artisan('articles:cleanup-test-data', ['--mode' => 'draft', '--force' => true])
        ->assertSuccessful();

    expect($junk->fresh()->status)->toBe('Draft');
});

it('flags an article that is Published without a publish date', function () {
    $junk = Article::factory()->publishedWithoutDate()->create();

    $this->artisan('articles:cleanup-test-data', ['--mode' => 'draft', '--force' => true])
        ->assertSuccessful();

    expect($junk->fresh()->status)->toBe('Draft');
});

it('leaves a valid published article untouched', function () {
    $good = Article::factory()->published()->create([
        'title' => ['en' => 'A Genuinely Real Article', 'id' => 'Artikel Sungguhan'],
    ]);

    $this->artisan('articles:cleanup-test-data', ['--mode' => 'draft', '--force' => true])
        ->assertSuccessful();

    expect($good->fresh()->status)->toBe('Published');
});

it('deletes junk articles in delete mode', function () {
    $junk = Article::factory()->published()->urlTitle()->create();
    $good = Article::factory()->published()->create();

    $this->artisan('articles:cleanup-test-data', ['--mode' => 'delete', '--force' => true])
        ->assertSuccessful();

    expect(Article::find($junk->id))->toBeNull()
        ->and(Article::find($good->id))->not->toBeNull();
});

it('changes nothing in dry-run mode', function () {
    $junk = Article::factory()->published()->urlTitle()->create();

    $this->artisan('articles:cleanup-test-data', ['--dry-run' => true])
        ->assertSuccessful();

    expect($junk->fresh()->status)->toBe('Published');
});

it('reports success and changes nothing when the table is clean', function () {
    Article::factory()->published()->create();

    $this->artisan('articles:cleanup-test-data', ['--mode' => 'delete', '--force' => true])
        ->expectsOutputToContain('No test/placeholder articles found')
        ->assertSuccessful();

    expect(Article::count())->toBe(1);
});

it('rejects an unknown --mode', function () {
    $this->artisan('articles:cleanup-test-data', ['--mode' => 'nuke'])
        ->assertFailed();
});

it('asks what to do when run without --mode and honours cancel', function () {
    $junk = Article::factory()->published()->urlTitle()->create();

    $this->artisan('articles:cleanup-test-data')
        ->expectsChoice(
            'What should be done with these articles?',
            'cancel',
            [
                'draft'  => 'Move to Draft (safe — keeps all data, hides them from /blog)',
                'delete' => 'Delete permanently (cannot be undone)',
                'cancel' => 'Cancel, change nothing',
            ],
        )
        ->assertSuccessful();

    expect($junk->fresh()->status)->toBe('Published');
});
