<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ─────────────────────────────────────────────────

    private function createPublishedArticle(array $overrides = []): Article
    {
        return Article::create(array_merge([
            'title'        => ['en' => 'Test Article Title', 'id' => 'Judul Artikel Test'],
            'slug'         => ['en' => 'test-article-title', 'id' => 'judul-artikel-test'],
            'content'      => ['en' => '<p>Test article content goes here.</p>', 'id' => '<p>Konten artikel test.</p>'],
            'status'       => 'Published',
            'published_at' => Carbon::now()->subDay(),
        ], $overrides));
    }

    private function createDraftArticle(array $overrides = []): Article
    {
        return Article::create(array_merge([
            'title'        => ['en' => 'Draft Article', 'id' => 'Draft Artikel'],
            'slug'         => ['en' => 'draft-article', 'id' => 'draft-artikel'],
            'content'      => ['en' => '<p>Draft content</p>', 'id' => '<p>Konten draft</p>'],
            'status'       => 'Draft',
            'published_at' => null,
        ], $overrides));
    }

    // ── Blog Index Tests ─────────────────────────────────────────

    /** @test */
    public function blog_index_returns_200(): void
    {
        $this->get(route('blog.index'))
             ->assertStatus(200)
             ->assertSee('Blog');
    }

    /** @test */
    public function blog_index_shows_only_published_articles(): void
    {
        $published = $this->createPublishedArticle(['slug' => ['en' => 'pub-article', 'id' => 'pub-article']]);
        $draft     = $this->createDraftArticle(['slug' => ['en' => 'dft-article', 'id' => 'dft-article']]);

        $response = $this->get(route('blog.index'));

        $response->assertStatus(200)
                 ->assertSee('Test Article Title')
                 ->assertDontSee('Draft Article');
    }

    /** @test */
    public function blog_index_search_filters_by_title(): void
    {
        $this->createPublishedArticle([
            'slug'  => ['en' => 'laravel-article', 'id' => 'laravel-article'],
            'title' => ['en' => 'Laravel is Amazing', 'id' => 'Laravel Luar Biasa'],
        ]);
        $this->createPublishedArticle([
            'slug'  => ['en' => 'python-article', 'id' => 'python-article'],
            'title' => ['en' => 'Python for Data Science', 'id' => 'Python untuk Sains Data'],
        ]);

        $response = $this->get(route('blog.index', ['search' => 'Laravel']));

        $response->assertStatus(200)
                 ->assertSee('Laravel is Amazing')
                 ->assertDontSee('Python for Data Science');
    }

    /** @test */
    public function blog_index_shows_empty_state_when_no_articles(): void
    {
        $this->get(route('blog.index'))
             ->assertStatus(200)
             ->assertSee('No articles yet');
    }

    /** @test */
    public function blog_index_search_shows_no_results_state(): void
    {
        $this->createPublishedArticle(['slug' => ['en' => 'some-article', 'id' => 'some-article']]);

        $this->get(route('blog.index', ['search' => 'nonexistentxyz']))
             ->assertStatus(200)
             ->assertSee('No articles found');
    }

    /** @test */
    public function blog_index_paginates_articles(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->createPublishedArticle([
                'slug'  => ['en' => "article-{$i}", 'id' => "artikel-{$i}"],
                'title' => ['en' => "Article Number {$i}", 'id' => "Artikel Nomor {$i}"],
            ]);
        }

        $response = $this->get(route('blog.index'));
        $response->assertStatus(200);
        // Page 2 should also work
        $this->get(route('blog.index', ['page' => 2]))
             ->assertStatus(200);
    }

    // ── Blog Show Tests ──────────────────────────────────────────

    /** @test */
    public function blog_show_returns_200_for_published_article(): void
    {
        $article = $this->createPublishedArticle(['slug' => ['en' => 'show-test-article', 'id' => 'show-test-article']]);

        $this->get(route('blog.show', $article->slug))
             ->assertStatus(200)
             ->assertSee('Test Article Title');
    }

    /** @test */
    public function blog_show_returns_404_for_draft_article(): void
    {
        $draft = $this->createDraftArticle(['slug' => ['en' => 'secret-draft', 'id' => 'secret-draft']]);

        $this->get(route('blog.show', $draft->slug))
             ->assertStatus(404);
    }

    /** @test */
    public function blog_show_returns_404_for_nonexistent_slug(): void
    {
        $this->get(route('blog.show', 'this-slug-does-not-exist'))
             ->assertStatus(404);
    }

    /** @test */
    public function blog_show_displays_article_content(): void
    {
        $article = $this->createPublishedArticle([
            'slug'    => ['en' => 'content-test', 'id' => 'content-test'],
            'content' => ['en' => '<p>Unique content for testing.</p>', 'id' => '<p>Konten unik untuk pengujian.</p>'],
        ]);

        $this->get(route('blog.show', $article->slug))
             ->assertStatus(200)
             ->assertSee('Unique content for testing.', false);  // false = don't escape
    }

    /** @test */
    public function blog_show_displays_related_articles(): void
    {
        $main    = $this->createPublishedArticle(['slug' => ['en' => 'main-article', 'id' => 'main-article']]);
        $related = $this->createPublishedArticle([
            'slug'  => ['en' => 'related-article', 'id' => 'related-article'],
            'title' => ['en' => 'Related Article Title', 'id' => 'Judul Artikel Terkait'],
        ]);

        $this->get(route('blog.show', $main->slug))
             ->assertStatus(200)
             ->assertSee('Related Article Title');
    }

    // ── Navigation Tests ─────────────────────────────────────────

    /** @test */
    public function homepage_blog_links_point_to_blog_route(): void
    {
        $this->get(route('home'))
             ->assertStatus(200)
             ->assertSee('href="/blog"', false);
    }
}
