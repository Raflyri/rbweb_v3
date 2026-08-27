<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL as UrlFacade;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Article;
use App\Models\User;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap for RBeverything';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // The sitemap is now regenerated from web requests too (see
        // App\Observers\Concerns\RefreshesSitemap), not just from the console.
        // url() would then inherit whatever scheme and host that request had —
        // http, or an internal hostname behind the proxy. Pin it to APP_URL so
        // every <loc> is identical no matter what triggered the rebuild.
        $base = (string) config('app.url');

        if ($base !== '') {
            UrlFacade::forceRootUrl($base);

            if (str_starts_with($base, 'https://')) {
                UrlFacade::forceScheme('https');
            }
        }

        $sitemap = Sitemap::create();

        // a. Static routes
        $sitemap->add(Url::create('/')
            ->setPriority(1.0)
            ->setLastModificationDate(now()));

        $sitemap->add(Url::create('/blog')
            ->setPriority(0.8)
            ->setLastModificationDate(now()));

        // b. Dynamic routes for Articles
        //
        // `slug` is a translatable JSON column, so $article->slug returns the
        // value for the active locale and is empty whenever that locale has
        // no translation — which produced "/blog/" entries. Resolve it with a
        // fallback and skip anything that still has no slug.
        $locale = \App\Support\ArticleLocale::current();

        foreach (Article::visiblePublic()->get() as $article) {
            $slug = $article->getTranslation('slug', $locale, true);

            if (! is_string($slug) || trim($slug) === '') {
                $this->warn("Skipped article #{$article->id}: no slug for any locale.");

                continue;
            }

            $sitemap->add(Url::create("/blog/{$slug}")
                ->setPriority(0.7)
                ->setLastModificationDate($article->updated_at ?? now()));
        }

        // c. Dynamic routes for Portfolios
        $profiles = \App\Models\Profile::whereNotNull('custom_url_slug')->get();
        foreach ($profiles as $profile) {
            $sitemap->add(Url::create("/@{$profile->custom_url_slug}")
                ->setPriority(0.6)
                ->setLastModificationDate($profile->updated_at ?? now()));
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $count = count($sitemap->getTags());

        $this->info("Sitemap generated at public/sitemap.xml ({$count} URLs, base " . config('app.url') . ').');

        if (str_contains((string) config('app.url'), 'localhost')) {
            $this->warn('APP_URL still points at localhost — every <loc> in this sitemap is wrong.');
        }

        return self::SUCCESS;
    }
}
