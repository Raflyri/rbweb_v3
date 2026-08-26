<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Support\ArticleLocale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-time data repair: rewrite wrong locale keys inside the translatable
 * JSON columns of `articles` to their ISO 639-1 equivalents.
 *
 *   'my'    → 'ms'    (Malay was stored under the code for Burmese)
 *   'jp'    → 'ja'    ('jp' is not a language code)
 *   'en_US' → 'en'    (APP_LOCALE default leaked into stored data)
 *
 * Values are only moved into an empty target. If the target key already holds
 * different text the legacy key is left alone and reported as a conflict, so
 * this command can never silently discard someone's writing.
 */
class FixArticleLocaleKeys extends Command
{
    protected $signature = 'articles:fix-locale-keys
                            {--dry-run : Report what would change without writing}
                            {--force : Skip the confirmation prompt (for HTTP/cron triggers)}';

    protected $description = 'Rewrite legacy locale keys (my/jp/en_US) in article JSON columns to en/id/ms/ja';

    /** @var array<int, string> */
    protected array $conflicts = [];

    public function handle(): int
    {
        $dryRun  = (bool) $this->option('dry-run');
        $planned = [];

        foreach (Article::query()->orderBy('id')->get() as $article) {
            if ($changes = $this->planFor($article)) {
                $planned[$article->id] = $changes;
            }
        }

        if ($planned === []) {
            $this->info('✅ All article locale keys are already canonical (en/id/ms/ja).');
            $this->reportConflicts();

            return self::SUCCESS;
        }

        $this->warn(sprintf('%d article(s) carry legacy locale keys:', count($planned)));
        $this->newLine();
        $this->table(
            ['Article', 'Column', 'From', 'To', 'Action'],
            collect($planned)->flatMap(
                fn (array $columns, int $id) => collect($columns)->flatMap(
                    fn (array $moves, string $column) => array_map(
                        fn (array $move) => ["#{$id}", $column, $move['from'], $move['to'], $move['action']],
                        $moves,
                    ),
                ),
            )->all(),
        );
        $this->newLine();

        if ($dryRun) {
            $this->comment('--dry-run given: no changes were made.');
            $this->reportConflicts();

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Apply these changes?', true)) {
            $this->comment('Cancelled — no changes were made.');

            return self::SUCCESS;
        }

        $updated = 0;

        foreach ($planned as $id => $columns) {
            $payload = [];

            foreach ($columns as $column => $moves) {
                $payload[$column] = json_encode(
                    $moves[0]['result'],
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                );
            }

            // Written straight through the query builder: this is a key rename,
            // not an edit, so it must not fire observers or bump updated_at.
            DB::table('articles')->where('id', $id)->update($payload);

            $this->line("  ✓ #{$id}: " . implode(', ', array_keys($columns)));
            $updated++;
        }

        $this->newLine();
        $this->info("✅ {$updated} article(s) migrated to canonical locale keys.");
        $this->reportConflicts();

        return self::SUCCESS;
    }

    /**
     * Work out the rewrites needed for one article.
     *
     * @return array<string, array<int, array{from: string, to: string, action: string, result: array}>>
     */
    protected function planFor(Article $article): array
    {
        $plan = [];

        foreach ($article->translatable as $column) {
            $raw = $article->getRawOriginal($column);

            if (! is_string($raw) || $raw === '') {
                continue;
            }

            $data = json_decode($raw, true);

            if (! is_array($data)) {
                // Pre-translatable rows stored a plain string; leave them be.
                continue;
            }

            $moves  = [];
            $result = $data;

            foreach ($data as $key => $value) {
                $key = (string) $key;

                if (ArticleLocale::isSupported($key)) {
                    continue;
                }

                $canonical = $this->canonicalFor($key);

                if ($canonical === null) {
                    $this->conflicts[] = "#{$article->id} {$column}: unknown locale key '{$key}' left untouched";

                    continue;
                }

                $existing = $result[$canonical] ?? null;

                if ($this->isBlank($value)) {
                    unset($result[$key]);
                    $moves[] = ['from' => $key, 'to' => $canonical, 'action' => 'drop (empty)', 'result' => []];

                    continue;
                }

                if ($this->isBlank($existing)) {
                    $result[$canonical] = $value;
                    unset($result[$key]);
                    $moves[] = ['from' => $key, 'to' => $canonical, 'action' => 'move', 'result' => []];

                    continue;
                }

                if ($existing === $value) {
                    unset($result[$key]);
                    $moves[] = ['from' => $key, 'to' => $canonical, 'action' => 'drop (duplicate)', 'result' => []];

                    continue;
                }

                $this->conflicts[] = "#{$article->id} {$column}: '{$key}' and '{$canonical}' both hold different text — '{$key}' kept, resolve by hand";
            }

            if ($moves === []) {
                continue;
            }

            // Every move for a column shares the same final array.
            foreach ($moves as $i => $move) {
                $moves[$i]['result'] = $result;
            }

            $plan[$column] = $moves;
        }

        return $plan;
    }

    /** Canonical key for a known-bad locale key, or null if unrecognised. */
    protected function canonicalFor(string $key): ?string
    {
        $lookup = strtolower(str_replace('-', '_', $key));

        return ArticleLocale::ALIASES[$lookup] ?? null;
    }

    protected function isBlank(mixed $value): bool
    {
        return ! is_string($value) || trim(strip_tags($value)) === '';
    }

    protected function reportConflicts(): void
    {
        if ($this->conflicts === []) {
            return;
        }

        $this->newLine();
        $this->warn('Needs a human decision:');

        foreach (array_unique($this->conflicts) as $line) {
            $this->line("  ! {$line}");
        }
    }
}
