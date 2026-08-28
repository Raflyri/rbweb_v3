<?php

use App\Models\Article;

/*
|--------------------------------------------------------------------------
| Blog publication visibility
|--------------------------------------------------------------------------
| Guards the rule that only Published articles are reachable publicly, and
| that /blog stays presentable in the degenerate cases (no articles at all,
| article without thumbnail/tags/body).
*/

it('returns 200 on /blog when there are no articles at all', function () {
    expect(Article::count())->toBe(0);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSee('No articles yet')
        ->assertSee(route('home'));
});

it('lists a published article on /blog', function () {
    $article = Article::factory()->published()->create([
        'title' => ['en' => 'Visible Published Article', 'id' => 'Artikel Terbit'],
        'slug'  => ['en' => 'visible-published-article', 'id' => 'visible-published-article'],
    ]);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSee('Visible Published Article');
});

it('hides draft and pending review articles from /blog', function () {
    Article::factory()->draft()->create([
        'title' => ['en' => 'Hidden Draft Article', 'id' => 'Hidden Draft Article'],
        'slug'  => ['en' => 'hidden-draft-article', 'id' => 'hidden-draft-article'],
    ]);

    Article::factory()->pendingReview()->create([
        'title' => ['en' => 'Hidden Pending Article', 'id' => 'Hidden Pending Article'],
        'slug'  => ['en' => 'hidden-pending-article', 'id' => 'hidden-pending-article'],
    ]);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertDontSee('Hidden Draft Article')
        ->assertDontSee('Hidden Pending Article')
        ->assertSee('No articles yet');
});

it('shows a published article at /blog/{slug}', function () {
    Article::factory()->published()->create([
        'title'   => ['en' => 'Deep Dive Into Caching', 'id' => 'Deep Dive Into Caching'],
        'slug'    => ['en' => 'deep-dive-into-caching', 'id' => 'deep-dive-into-caching'],
        'content' => ['en' => '<p>Cache invalidation is hard.</p>', 'id' => '<p>Cache invalidation is hard.</p>'],
    ]);

    $this->get('/blog/deep-dive-into-caching')
        ->assertOk()
        ->assertSee('Deep Dive Into Caching')
        ->assertSee('Cache invalidation is hard.', false);
});

it('returns 404 for a draft article accessed directly by slug', function () {
    Article::factory()->draft()->create([
        'slug' => ['en' => 'secret-draft', 'id' => 'secret-draft'],
    ]);

    $this->get('/blog/secret-draft')->assertNotFound();
});

it('renders an article that has no thumbnail or tags without crashing', function () {
    // Visible-but-minimal content, not blank markup: ArticleObserver::
    // pruneBlankLocales() now strips a locale whose content is empty-
    // looking markup like "<p></p>" before it ever reaches storage (see
    // ArticleLocaleVisibilityTest), so an article cannot actually exist
    // with real HTML that renders to zero visible text — the "This
    // article has no content yet" placeholder in blog/show.blade.php is
    // dead code kept as a defensive fallback, not a reachable state. This
    // test is about thumbnail/tags being absent, not about empty content.
    Article::factory()->published()->create([
        'title'     => ['en' => 'Bare Bones Article', 'id' => 'Bare Bones Article'],
        'slug'      => ['en' => 'bare-bones-article', 'id' => 'bare-bones-article'],
        'content'   => ['en' => '<p>x</p>', 'id' => '<p>x</p>'],
        'excerpt'   => ['en' => '', 'id' => ''],
        'thumbnail' => null,
    ]);

    $response = $this->get('/blog/bare-bones-article');

    $response->assertOk()
        ->assertSee('Bare Bones Article')
        ->assertSee('1 min read');
});
