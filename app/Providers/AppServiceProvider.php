<?php

namespace App\Providers;

use App\Listeners\AssignClientRoleOnRegister;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;
use App\Models\Article;
use App\Models\Post;
use App\Models\Product;
use App\Models\Profile;
use App\Observers\ArticleObserver;
use App\Observers\PostObserver;
use App\Observers\ProductObserver;
use App\Observers\ProfileObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ Auto-assign role 'regular_user' saat user baru register via Client Area
        Event::listen(Registered::class, AssignClientRoleOnRegister::class);

        // ✅ Track every login and logout for the Authentication Monitor
        Event::listen(Login::class,  LogSuccessfulLogin::class);
        Event::listen(Logout::class, LogSuccessfulLogout::class);

        // ✅ Kirim notifikasi saat status post berubah menjadi Published/Rejected
        Post::observe(PostObserver::class);

        // ✅ Backfill excerpt/meta_description dari konten saat artikel disimpan
        Article::observe(ArticleObserver::class);

        // ✅ Jaga sitemap.xml tetap sinkron dengan profil publik /@{slug}
        Profile::observe(ProfileObserver::class);

        // ✅ Sanitasi deskripsi produk saat disimpan + jaga sitemap tetap sinkron
        Product::observe(ProductObserver::class);

        // ✅ Register nested language lines from lang/{locale}.json into the translator
        $this->registerJsonTranslations();
    }

    /**
     * Register nested language keys from lang/{locale}.json so Blade views can
     * call __('catalog.title'), __('nav.home'), etc.
     */
    protected function registerJsonTranslations(): void
    {
        $translator = $this->app['translator'];

        foreach (['id', 'en'] as $locale) {
            $path = lang_path("{$locale}.json");
            if (file_exists($path)) {
                $content = json_decode(file_get_contents($path), true);
                if (is_array($content)) {
                    $dotLines = [];
                    foreach ($content as $key => $value) {
                        if (is_array($value)) {
                            foreach (\Illuminate\Support\Arr::dot([$key => $value]) as $dotKey => $dotValue) {
                                if (is_string($dotValue)) {
                                    $dotLines[$dotKey] = $dotValue;
                                }
                            }
                        }
                    }
                    if (! empty($dotLines)) {
                        $translator->addLines($dotLines, $locale);
                    }
                }
            }
        }
    }
}
