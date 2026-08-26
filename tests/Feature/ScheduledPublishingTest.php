<?php

use App\Models\Article;

/*
|--------------------------------------------------------------------------
| Scheduled → Published
|--------------------------------------------------------------------------
*/

it('publishes a scheduled article whose time has passed', function () {
    $article = Article::factory()->scheduled(now()->subHour())->create([
        'title' => ['en' => 'Due Scheduled Article', 'id' => 'Due Scheduled Article'],
        'slug'  => ['en' => 'due-scheduled-article', 'id' => 'due-scheduled-article'],
    ]);

    $this->artisan('app:publish-scheduled-articles')->assertSuccessful();

    expect($article->fresh()->status)->toBe('Published');

    $this->get(route('blog.index'))->assertOk()->assertSee('Due Scheduled Article');
});

it('leaves a future scheduled article alone and keeps it off /blog', function () {
    $article = Article::factory()->scheduled(now()->addWeek())->create([
        'title' => ['en' => 'Future Scheduled Article', 'id' => 'Future Scheduled Article'],
        'slug'  => ['en' => 'future-scheduled-article', 'id' => 'future-scheduled-article'],
    ]);

    $this->artisan('app:publish-scheduled-articles')->assertSuccessful();

    expect($article->fresh()->status)->toBe('Scheduled');

    $this->get(route('blog.index'))->assertOk()->assertDontSee('Future Scheduled Article');
    $this->get('/blog/future-scheduled-article')->assertNotFound();
});

it('ignores a scheduled article with no publish date', function () {
    $article = Article::factory()->create([
        'status'       => 'Scheduled',
        'published_at' => null,
    ]);

    $this->artisan('app:publish-scheduled-articles')->assertSuccessful();

    expect($article->fresh()->status)->toBe('Scheduled');
});

it('reports cleanly when nothing is due', function () {
    Article::factory()->published()->create();

    $this->artisan('app:publish-scheduled-articles')
        ->expectsOutputToContain('No scheduled articles are due')
        ->assertSuccessful();
});

it('shows a due scheduled article even before the command runs', function () {
    // Guards the race condition: on shared hosting the scheduler can lag, and
    // an article past its publish time must not stay hidden because of it.
    Article::factory()->scheduled(now()->subMinute())->create([
        'title' => ['en' => 'Race Condition Article', 'id' => 'Race Condition Article'],
        'slug'  => ['en' => 'race-condition-article', 'id' => 'race-condition-article'],
    ]);

    $this->get(route('blog.index'))->assertOk()->assertSee('Race Condition Article');
    $this->get('/blog/race-condition-article')->assertOk();
});
