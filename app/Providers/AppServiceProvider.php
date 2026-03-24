<?php

namespace App\Providers;

use App\Listeners\AssignClientRoleOnRegister;
use App\Models\Post;
use App\Observers\PostObserver;
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
        Event::listen(
            Registered::class,
            AssignClientRoleOnRegister::class,
        );

        // ✅ Kirim notifikasi saat status post berubah menjadi Published/Rejected
        Post::observe(PostObserver::class);
    }
}
