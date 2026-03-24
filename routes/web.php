<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\EmergencyCommandController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ArticleController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// ── Blog Public Routes ──────────────────────────────────────────
Route::get('/blog', [ArticleController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [ArticleController::class, 'show'])->name('blog.show');

// Emergency route to execute commands without SSH
Route::post('/system/emergency-command', [EmergencyCommandController::class, 'run'])
    ->name('system.emergency.command')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');
Route::get('/@{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');
