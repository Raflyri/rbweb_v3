<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $title = rtrim($this->faker->sentence(6), '.');
        $slug  = Str::slug($title) . '-' . Str::lower(Str::random(6));

        return [
            'user_id'      => null,
            'title'        => ['en' => $title, 'id' => $title],
            'slug'         => ['en' => $slug, 'id' => $slug],
            'content'      => ['en' => $this->body(), 'id' => $this->body()],
            'excerpt'      => ['en' => $this->faker->sentence(12), 'id' => $this->faker->sentence(12)],
            'thumbnail'    => 'article-thumbnails/example.jpg',
            'status'       => 'Draft',
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status'       => 'Published',
            'published_at' => now()->subDay(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status'       => 'Draft',
            'published_at' => null,
        ]);
    }

    public function pendingReview(): static
    {
        return $this->state(fn () => [
            'status'       => 'Pending Review',
            'published_at' => null,
        ]);
    }

    public function scheduled(?\DateTimeInterface $at = null): static
    {
        return $this->state(fn () => [
            'status'       => 'Scheduled',
            'published_at' => $at ?? now()->addDay(),
        ]);
    }

    /** An article whose title is a bare URL — the classic smoke-test row. */
    public function urlTitle(): static
    {
        return $this->state(fn () => [
            'title' => [
                'en' => 'https://rbeverything.com/blog/article',
                'id' => 'https://rbeverything.com/blog/article',
            ],
        ]);
    }

    /** An article with effectively no body. */
    public function emptyContent(): static
    {
        return $this->state(fn () => [
            'content' => ['en' => '<p>test</p>', 'id' => '<p>test</p>'],
        ]);
    }

    /** Published but with no publish date — an inconsistent, junk row. */
    public function publishedWithoutDate(): static
    {
        return $this->state(fn () => [
            'status'       => 'Published',
            'published_at' => null,
        ]);
    }

    /** Roughly 60 words — comfortably above both content thresholds. */
    protected function body(): string
    {
        return '<p>' . implode(' ', $this->faker->words(60)) . '</p>';
    }
}
