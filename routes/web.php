<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\EmergencyCommandController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ProductController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// ── Blog Public Routes ──────────────────────────────────────────
Route::get('/blog', [ArticleController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [ArticleController::class, 'show'])->name('blog.show');

// ── Catalogue Public Routes ─────────────────────────────────────
// Indonesian path on purpose: these pages are aimed squarely at the local
// market that buys them. The route names stay English to match blog.index /
// blog.show, so every route() call in the codebase reads the same way.
Route::get('/produk-layanan', [ProductController::class, 'index'])->name('products.index');
Route::get('/produk-layanan/{slug}', [ProductController::class, 'show'])->name('products.show');

// Emergency route to execute commands without SSH
Route::post('/system/emergency-command', [EmergencyCommandController::class, 'run'])
    ->name('system.emergency.command')
    ->middleware('throttle:5,1')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');
Route::get('/@{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');
