<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\EmergencyCommandController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\StorageFileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Payment\MidtransNotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AboutController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// ── About Public Routes ─────────────────────────────────────────
Route::get('/tentang-kami', [AboutController::class, 'index'])->name('about');
Route::redirect('/about', '/tentang-kami');

// ── Document Signature Verification ─────────────────────────────
Route::get('/signed/rb', function () {
    return view('signed.rb');
})->name('signed.rb');

// ── Blog Public Routes ──────────────────────────────────────────
Route::get('/blog', [ArticleController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [ArticleController::class, 'show'])->name('blog.show');

// ── Catalogue Public Routes ─────────────────────────────────────
// Indonesian path on purpose: these pages are aimed squarely at the local
// market that buys them. The route names stay English to match blog.index /
// blog.show, so every route() call in the codebase reads the same way.
Route::get('/produk-layanan', [ProductController::class, 'index'])->name('products.index');

// ── Shopping Cart (Keranjang Belanja) ───────────────────────────
Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang/tambah/{product:slug}', [CartController::class, 'add'])->name('cart.add');
Route::post('/keranjang/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/hapus/{productId}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/keranjang/kosongkan', [CartController::class, 'clear'])->name('cart.clear');

// ── Multi-Item Checkout ─────────────────────────────────────────
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('checkout.store');

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

// Uploading a transfer receipt. Unauthenticated by design — the person paying
// has no account — so the token is the key and the throttle is the fence.
Route::post('/pesanan/{order:public_token}/bukti-transfer', [OrderController::class, 'uploadProof'])
    ->middleware('throttle:6,1')
    ->name('order.proof.upload');

// Reading one back. The file is stored off the web root, so this route is the
// only way to it, and OrderController::proof() locks it to staff.
Route::get('/pesanan/{order:public_token}/bukti-transfer', [OrderController::class, 'proof'])
    ->name('order.proof');

// Midtrans Core API direct payment action
Route::post('/pesanan/{order:public_token}/bayar', [OrderController::class, 'chargeMidtrans'])
    ->middleware('throttle:15,1')
    ->name('order.midtrans.charge');

// Reset chosen payment method to choose another channel
Route::post('/pesanan/{order:public_token}/ganti-metode', [OrderController::class, 'resetPaymentMethod'])
    ->name('order.midtrans.reset');

// Status polling or manual refresh by buyer
Route::get('/pesanan/{order:public_token}/status', [OrderController::class, 'checkStatus'])
    ->middleware('throttle:60,1')
    ->name('order.status');

Route::get('/produk-layanan/{slug}', [ProductController::class, 'show'])->name('products.show');

// ── Payment webhook ─────────────────────────────────────────────
// Called by Midtrans' servers, not a browser, so it is exempt from CSRF the
// same way the emergency-command endpoint is. It refuses everything while
// MIDTRANS_IS_ACTIVE is false, and verifies the SHA512 signature before it
// reads anything else out of the request.
Route::post('/payment/midtrans/notification', [MidtransNotificationController::class, 'handle'])
    ->name('payment.midtrans.notification')
    ->middleware('throttle:60,1')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

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
