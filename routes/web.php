<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\EmergencyCommandController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\OrderController;
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

// ── Ordering ────────────────────────────────────────────────────
// Declared before /produk-layanan/{slug} so "pesan" is never swallowed by the
// product-detail wildcard.
Route::get('/produk-layanan/{product:slug}/pesan', [OrderController::class, 'create'])
    ->name('order.create');

// The form is public and unauthenticated, so it is throttled — the same
// posture as the emergency-command endpoint, just a looser limit because real
// buyers do occasionally submit twice.
Route::post('/produk-layanan/{product:slug}/pesan', [OrderController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('order.store');

// Keyed by an unguessable token, not the readable order number: this page
// shows the buyer's name, phone and address (see OrderController::pending).
Route::get('/pesanan/{order:public_token}', [OrderController::class, 'pending'])
    ->name('order.pending');

Route::get('/produk-layanan/{slug}', [ProductController::class, 'show'])->name('products.show');

// Emergency route to execute commands without SSH
Route::post('/system/emergency-command', [EmergencyCommandController::class, 'run'])
    ->name('system.emergency.command')
    ->middleware('throttle:5,1')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');
Route::get('/@{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');
