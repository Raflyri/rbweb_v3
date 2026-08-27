<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Support\ArticleContent;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Find leftover smoke-test articles and either archive them as drafts or
 * delete them outright.
 *
 * The blog went live with a single row whose title was literally a URL and
 * whose body was empty, which made /blog look broken. This command sweeps
 * that class of row out and is safe to re-run: articles that look like real
 * writing are never touched.
 */
class CleanupArticleTestData extends Command
{
    protected $signature = 'articles:cleanup-test-data
                            {--mode= : draft|delete — skip the interactive prompt}
                            {--dry-run : Only report what would happen, change nothing}
                            {--force : Do not ask for confirmation (for HTTP/cron triggers)}';

    protected $description = 'Detect placeholder/test articles (URL titles, near-empty content, published without a date) and draft or delete them';

    public function handle(): int
    {
        $mode = $this->option('mode');

        if ($mode !== null && ! in_array($mode, ['draft', 'delete'], true)) {
            $this->error("Invalid --mode '{$mode}'. Use 'draft' or 'delete'.");

            return self::INVALID;
        }

        $candidates = $this->findCandidates();

        if ($candidates->isEmpty()) {
            $this->info('✅ No test/placeholder articles found — nothing to clean up.');

            return self::SUCCESS;
        }

        $this->warn("Found {$candidates->count()} article(s) that look like test data:");
        $this->newLine();
        $this->table(
            ['ID', 'Title', 'Status', 'Words', 'Why it was flagged'],
            $candidates->map(fn (array $row) => [
                $row['article']->id,
                $this->previewTitle($row['article']),
                $row['article']->status,
                $row['words'],
                implode('; ', $row['reasons']),
            ])->all(),
        );
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->comment('--dry-run given: no changes were made.');

            return self::SUCCESS;
        }

        $mode ??= $this->resolveModeInteractively();

        if ($mode === null) {
            $this->comment('Cancelled — no changes were made.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirmAction($mode, $candidates->count())) {
            $this->comment('Cancelled — no changes were made.');

            return self::SUCCESS;
        }

        return $mode === 'delete'
            ? $this->deleteAll($candidates)
            : $this->draftAll($candidates);
    }

    /**
     * Articles matching at least one test-data signature.
     *
     * @return Collection<int, array{article: Article, words: int, reasons: array<int, string>}>
     */
    protected function findCandidates(): Collection
    {
        return Article::query()
            ->orderBy('id')
            ->get()
            ->map(fn (Article $article) => [
                'article' => $article,
                'words'   => ArticleContent::bestWordCount($article->getTranslations('content')),
                'reasons' => $this->reasonsFor($article),
            ])
            ->filter(fn (array $row) => $row['reasons'] !== [])
            ->values();
    }

    /**
     * @return array<int, string>
     */
    protected function reasonsFor(Article $article): array
    {
        $reasons = [];

        if (ArticleContent::looksLikeUrl($article->getTranslations('title'))) {
            $reasons[] = 'title is a URL';
        }

        $words = ArticleContent::bestWordCount($article->getTranslations('content'));

        if ($words < ArticleContent::TEST_DATA_MAX_WORDS) {
            $reasons[] = "content only {$words} word(s)";
        }

        if ($article->status === 'Published' && $article->published_at === null) {
            $reasons[] = 'Published without a publish date';
        }

        return $reasons;
    }

    protected function resolveModeInteractively(): ?string
    {
        $choice = $this->choice(
            'What should be done with these articles?',
            [
                'draft'  => 'Move to Draft (safe — keeps all data, hides them from /blog)',
                'delete' => 'Delete permanently (cannot be undone)',
                'cancel' => 'Cancel, change nothing',
            ],
            'draft',
        );

        return $choice === 'cancel' ? null : $choice;
    }

    protected function confirmAction(string $mode, int $count): bool
    {
        return $mode === 'delete'
            ? $this->confirm("Permanently DELETE {$count} article(s)? This cannot be undone.", false)
            : $this->confirm("Move {$count} article(s) to Draft?", true);
    }

    /**
     * @param  Collection<int, array{article: Article, words: int, reasons: array<int, string>}> $candidates
     */
    protected function draftAll(Collection $candidates): int
    {
        $changed = 0;

        foreach ($candidates as $row) {
            /** @var Article $article */
            $article = $row['article'];

            if ($article->status === 'Draft') {
                $this->line("  – #{$article->id} already Draft, skipped.");

                continue;
            }

            // published_at is deliberately left intact so the change stays
            // reversible if one of these turns out to be a real article.
            $article->status = 'Draft';
            $article->save();

            $this->line("  ✓ #{$article->id} moved to Draft.");
            $changed++;
        }

        $this->newLine();
        $this->info("✅ {$changed} article(s) moved to Draft.");

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, array{article: Article, words: int, reasons: array<int, string>}> $candidates
     */
    protected function deleteAll(Collection $candidates): int
    {
        $deleted = 0;

        foreach ($candidates as $row) {
            /** @var Article $article */
            $article = $row['article'];
            $id = $article->id;

            $article->tags()->detach();
            $article->delete();

            $this->line("  ✓ #{$id} deleted.");
            $deleted++;
        }

        $this->newLine();
        $this->info("✅ {$deleted} article(s) deleted.");

        return self::SUCCESS;
    }

    protected function previewTitle(Article $article): string
    {
        foreach ($article->getTranslations('title') as $value) {
            if (is_string($value) && trim($value) !== '') {
                return \Illuminate\Support\Str::limit($value, 40);
            }
        }

        return '(no title)';
    }
}
