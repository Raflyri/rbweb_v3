<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
    public function handle()
    {
        $sitemap = Sitemap::create();

        // a. Static routes
        $sitemap->add(Url::create('/')
            ->setPriority(1.0)
            ->setLastModificationDate(now()));

        $sitemap->add(Url::create('/blog')
            ->setPriority(0.8)
            ->setLastModificationDate(now()));

        // b. Dynamic routes for Articles
        $articles = Article::published()->get();
        foreach ($articles as $article) {
            $sitemap->add(Url::create("/blog/{$article->slug}")
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

        $this->info('Sitemap generated successfully at public/sitemap.xml');
    }
}
