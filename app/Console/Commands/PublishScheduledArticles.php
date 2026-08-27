<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

/**
 * Promote Scheduled articles to Published once their publish time arrives.
 *
 * scopePublished() deliberately only looks at `status`, so this command is
 * what keeps that column truthful. It is idempotent and cheap enough to run
 * every minute, either from the Laravel scheduler or — on shared hosting with
 * no reliable cron — from a cPanel cron job hitting /system/emergency-command.
 */
class PublishScheduledArticles extends Command
{
    protected $signature   = 'app:publish-scheduled-articles';
    protected $description = 'Publish articles whose Scheduled publish time has passed';

    public function handle(): int
    {
        $due = Article::query()
            ->where('status', 'Scheduled')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get();

        if ($due->isEmpty()) {
            $this->info('No scheduled articles are due.');

            return self::SUCCESS;
        }

        foreach ($due as $article) {
            $article->status = 'Published';
            $article->save();

            $this->line("  ✓ #{$article->id} published (scheduled for {$article->published_at}).");
        }

        $this->info("✅ Published {$due->count()} scheduled article(s).");

        return self::SUCCESS;
    }
}
