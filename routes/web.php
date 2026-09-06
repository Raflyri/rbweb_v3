<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\EmergencyCommandController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\StorageFileController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// ── Blog Public Routes ──────────────────────────────────────────
Route::get('/blog', [ArticleController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [ArticleController::class, 'show'])->name('blog.show');

// Emergency route to execute commands without SSH
Route::post('/system/emergency-command', [EmergencyCommandController::class, 'run'])
    ->name('system.emergency.command')
    ->middleware('throttle:5,1')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');
Route::get('/@{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');

// ── Public disk files ───────────────────────────────────────────
// Stands in for the public/storage symlink, which cannot exist here: this
// host disables both symlink() and exec(), so Laravel's storage:link has no
// working code path. Registered last so it never shadows a real route, and
// inert anywhere the symlink does exist, since the web server answers those
// requests before PHP is reached.
Route::get('/storage/{path}', [StorageFileController::class, 'show'])
    ->where('path', '.*')
    ->name('storage.file');
