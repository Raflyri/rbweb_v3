<?php

namespace App\Observers\Concerns;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Regenerate public/sitemap.xml when public content changes.
 *
 * The scheduler already has a daily app:generate-sitemap task, but it needs a
 * `schedule:run` cron this shared host does not reliably provide — which is
 * why the live sitemap sat five months stale. Refreshing on write removes the
 * dependency on cron entirely.
 *
 * The work is deferred to app termination so a save never waits on it, and is
 * coalesced so a bulk operation writes the file once rather than once per row.
 * Failures are logged, never thrown: a sitemap problem must not stop someone
 * saving an article.
 */
trait RefreshesSitemap
{
    /**
     * Container key marking "a refresh is already queued for this request".
     *
     * Deliberately container state rather than a static property: a static
     * would only be cleared by the terminating callback, so any process that
     * did not terminate cleanly — a queue worker, a long-lived console
     * command — would keep the flag set and silently skip every later refresh.
     * The container is rebuilt per request, so this resets on its own.
     */
    protected const SITEMAP_PENDING_KEY = 'sitemap.refresh.pending';

    protected function refreshSitemapSoon(): void
    {
        if (! config('app.auto_sitemap', true)) {
            return;
        }

        $app = app();

        if ($app->bound(static::SITEMAP_PENDING_KEY)) {
            return;
        }

        $app->instance(static::SITEMAP_PENDING_KEY, true);

        $app->terminating(function (): void {
            try {
                Artisan::call('app:generate-sitemap');
            } catch (\Throwable $e) {
                Log::warning('Automatic sitemap refresh failed', ['error' => $e->getMessage()]);
            }
        });
    }
}
