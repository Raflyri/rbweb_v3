<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

/**
 * Quick production audit that can be run over HTTP through
 * /system/emergency-command, since there is no SSH on this host.
 *
 * Answers the questions that silently break SEO and are invisible from the
 * outside: is APP_URL right, is debug mode on, is the sitemap stale, does it
 * actually contain the articles.
 */
class CheckSeoConfig extends Command
{
    protected $signature   = 'app:check-seo-config {--max-age=7 : Days before sitemap.xml counts as stale}';
    protected $description = 'Audit APP_URL, APP_DEBUG and sitemap freshness';

    public function handle(): int
    {
        $problems = 0;
        $maxAge   = max(1, (int) $this->option('max-age'));

        $this->line('');
        $this->line('SEO configuration audit');
        $this->line('───────────────────────');

        // ── APP_URL ──────────────────────────────────────────────────
        $url = (string) config('app.url');
        $this->line("APP_URL          : {$url}");

        if ($url === '' || str_contains($url, 'localhost') || str_contains($url, '127.0.0.1')) {
            $this->error('  ✗ APP_URL is not a public URL. Every <loc> in sitemap.xml and every absolute link will be wrong.');
            $problems++;
        } elseif (! str_starts_with($url, 'https://')) {
            $this->warn('  ! APP_URL is not https.');
            $problems++;
        } elseif (str_ends_with($url, '/')) {
            $this->warn('  ! APP_URL has a trailing slash; this produces double slashes in generated URLs.');
            $problems++;
        } else {
            $this->info('  ✓ looks correct');
        }

        // ── APP_DEBUG ────────────────────────────────────────────────
        $debug = (bool) config('app.debug');
        $env   = (string) config('app.env');
        $this->line("APP_ENV / DEBUG  : {$env} / " . ($debug ? 'true' : 'false'));

        if ($debug && $env === 'production') {
            $this->error('  ✗ APP_DEBUG is true in production — stack traces leak configuration and credentials.');
            $problems++;
        } elseif ($debug) {
            $this->warn('  ! APP_DEBUG is true.');
        } else {
            $this->info('  ✓ debug is off');
        }

        // ── sitemap.xml ──────────────────────────────────────────────
        $path = public_path('sitemap.xml');

        if (! file_exists($path)) {
            $this->line('sitemap.xml      : missing');
            $this->error('  ✗ sitemap.xml has never been generated. Run app:generate-sitemap.');
            $problems++;
        } else {
            $age      = (int) floor((time() - filemtime($path)) / 86400);
            $contents = (string) file_get_contents($path);
            $urls     = substr_count($contents, '<loc>');
            $expected = Article::visiblePublic()->count() + 2; // home + /blog

            $this->line("sitemap.xml      : {$urls} URLs, {$age} day(s) old");

            if ($age > $maxAge) {
                $this->error("  ✗ older than {$maxAge} days. Run app:generate-sitemap.");
                $problems++;
            }

            if (str_contains($contents, 'localhost')) {
                $this->error('  ✗ contains localhost URLs — fix APP_URL, then regenerate.');
                $problems++;
            }

            if ($urls < $expected) {
                $this->warn("  ! only {$urls} URLs but {$expected} were expected (public articles + home + /blog).");
                $problems++;
            }

            if ($age <= $maxAge && ! str_contains($contents, 'localhost') && $urls >= $expected) {
                $this->info('  ✓ fresh and complete');
            }
        }

        // ── robots.txt ───────────────────────────────────────────────
        $robots = public_path('robots.txt');

        if (! file_exists($robots)) {
            $this->line('robots.txt       : missing');
            $this->warn('  ! no robots.txt.');
            $problems++;
        } else {
            $contents = (string) file_get_contents($robots);
            $this->line('robots.txt       : present');

            foreach (['/rbdashboard/', '/client-area/'] as $mustBlock) {
                if (! str_contains($contents, "Disallow: {$mustBlock}")) {
                    $this->error("  ✗ does not disallow {$mustBlock}");
                    $problems++;
                }
            }
        }

        $this->line('');

        if ($problems === 0) {
            $this->info('✅ No SEO configuration problems found.');

            return self::SUCCESS;
        }

        $this->warn("⚠  {$problems} problem(s) found — see above.");

        // Deliberately SUCCESS: this is a report, and returning a failure code
        // would surface as a 500 from the emergency endpoint.
        return self::SUCCESS;
    }
}
