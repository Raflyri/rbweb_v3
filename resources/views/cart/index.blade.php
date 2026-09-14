@extends('layouts.public')

@section('meta_title', 'Keranjang Belanja — RBeverything')
@section('meta_description', 'Keranjang belanja Anda di RBeverything. Periksa produk pilihan Anda dan lanjutkan ke proses checkout.')
@section('meta_robots', 'noindex, nofollow')

@section('content')
<section class="cart-section" aria-label="Keranjang Belanja">
    <div class="rb-section" style="padding-bottom:5rem;">

        <nav class="catalog-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="catalog-breadcrumb__link">{{ __('nav.home') }}</a>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
            <a href="{{ route('products.index') }}" class="catalog-breadcrumb__link">{{ __('catalog.title') }}</a>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
            <span class="catalog-breadcrumb__current">Keranjang Belanja</span>
        </nav>

        <span class="rb-section-label">E-Commerce</span>
        <h1 class="cart-title">Keranjang Belanja</h1>
        <p class="cart-lead">Periksa barang dan layanan yang telah Anda pilih sebelum melanjutkan ke tahap pembayaran.</p>

        @if(session('cart_success'))
            <div class="cart-alert cart-alert--ok" role="status">{{ session('cart_success') }}</div>
        @endif
        @if(session('cart_info'))
            <div class="cart-alert cart-alert--info" role="status">{{ session('cart_info') }}</div>
        @endif
        @if(session('cart_error'))
            <div class="cart-alert cart-alert--warn" role="alert">{{ session('cart_error') }}</div>
        @endif

        @if($isEmpty)
            <div class="cart-empty-state">
                <div class="cart-empty-icon" aria-hidden="true">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                </div>
                <h2 class="cart-empty-title">Keranjang Anda Masih Kosong</h2>
                <p class="cart-empty-desc">Jelajahi produk teknologi, sparepart, dan layanan digital kami lalu tambahkan ke keranjang.</p>
                <a href="{{ route('products.index') }}" class="rb-btn-primary" style="margin-top:1.25rem;">
                    Jelajahi Katalog Produk &rarr;
                </a>
            </div>
        @else
            <div class="cart-layout">
                {{-- ── Items Table / List ── --}}
                <div class="cart-items-panel">
                    <div class="cart-table-header">
                        <span>Produk</span>
                        <span>Harga Satuan</span>
                        <span style="text-align:center;">Jumlah</span>
                        <span style="text-align:right;">Subtotal</span>
                        <span></span>
                    </div>

                    <div class="cart-items-list">
                        @foreach($items as $item)
                            <div class="cart-item-row" data-id="{{ $item['product_id'] }}">
                                <div class="cart-item-info">
                                    @if($item['thumbnail'])
                                        <img src="{{ $item['thumbnail'] }}" alt="{{ $item['name'] }}" class="cart-item-img">
                                    @else
                                        <div class="cart-item-img cart-item-img--placeholder">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(220,38,38,0.3)" stroke-width="1.5">
                                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="cart-item-details">
                                        <a href="{{ route('products.show', $item['slug']) }}" class="cart-item-name">
                                            {{ $item['name'] }}
                                        </a>
                                        <span class="cart-item-type-badge cart-item-type-badge--{{ $item['type'] }}">
                                            {{ $item['type'] === 'barang' ? 'Produk Fisik' : 'Layanan Jasa' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="cart-item-price">
                                    {{ $item['formatted_price'] }}
                                </div>

                                <div class="cart-item-qty">
                                    <form method="POST" action="{{ route('cart.update') }}" class="cart-qty-form">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                        <div class="cart-qty-control">
                                            <button type="button" class="cart-qty-btn" onclick="updateQty(this, -1)">-</button>
                                            <input type="number" name="qty" value="{{ $item['qty'] }}" min="1"
                                                   max="{{ $item['tracks_stock'] && $item['stock'] ? $item['stock'] : 999 }}"
                                                   class="cart-qty-input" onchange="this.form.submit()">
                                            <button type="button" class="cart-qty-btn" onclick="updateQty(this, 1)">+</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="cart-item-subtotal">
                                    {{ $item['formatted_subtotal'] }}
                                </div>

                                <div class="cart-item-actions">
                                    <form method="POST" action="{{ route('cart.remove', $item['product_id']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cart-remove-btn" title="Hapus produk ini" aria-label="Hapus produk">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="cart-footer-actions">
                        <a href="{{ route('products.index') }}" class="cart-back-link">
                            &larr; Lanjut Belanja
                        </a>
                        <form method="POST" action="{{ route('cart.clear') }}">
                            @csrf
                            <button type="submit" class="cart-clear-btn" onclick="return confirm('Kosongkan semua isi keranjang?')">
                                Kosongkan Keranjang
                            </button>
                        </form>
                    </div>
                </div>

                {{-- ── Order Summary Card ── --}}
                <aside class="cart-summary-panel">
                    <h2 class="cart-summary-title">Ringkasan Belanja</h2>

                    <div class="cart-summary-row">
                        <span>Total Item</span>
                        <strong>{{ $count }} item</strong>
                    </div>

                    <div class="cart-summary-row">
                        <span>Subtotal Produk</span>
                        <strong class="cart-summary-amount">{{ $subtotal }}</strong>
                    </div>

                    @if($hasPhysicalItems)
                        <div class="cart-summary-row cart-summary-row--note">
                            <span>Biaya Pengiriman</span>
                            <span>Dikonfirmasi saat checkout</span>
                        </div>
                    @endif

                    <div class="cart-summary-row cart-summary-row--total">
                        <span>Total Tagihan</span>
                        <strong class="cart-summary-total">{{ $subtotal }}</strong>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="rb-btn-primary cart-checkout-btn">
                        Lanjut ke Pembayaran
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <div class="cart-trust-badges">
                        <div class="cart-trust-badge">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <span>Pembayaran Aman & Terverifikasi</span>
                        </div>
                        <div class="cart-trust-badge">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                            <span>QRIS & Virtual Account Otomatis</span>
                        </div>
                    </div>
                </aside>
            </div>
        @endif

    </div>
</section>
@endsection

@section('styles')
<style>
.catalog-breadcrumb {
    display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;
    font-size: 0.8rem; color: var(--color-muted); margin-bottom: 2rem;
}
.catalog-breadcrumb__link { color: var(--color-muted); text-decoration: none; transition: color 0.25s ease; }
.catalog-breadcrumb__link:hover { color: var(--color-text); }
.catalog-breadcrumb__current { color: var(--color-text); font-weight: 500; }

.cart-title {
    font-size: clamp(1.75rem, 4vw, 2.75rem);
    font-weight: 900; letter-spacing: -0.025em; line-height: 1.15;
    color: #F5F5F5; margin: 0 0 0.75rem;
}
.cart-lead {
    font-size: 1rem; color: var(--color-muted); line-height: 1.75;
    max-width: 36rem; margin: 0 0 2.5rem;
}

.cart-alert {
    padding: 0.85rem 1.15rem; border-radius: 0.75rem; font-size: 0.875rem; margin-bottom: 1.5rem;
}
.cart-alert--ok {
    border: 1px solid rgba(52,211,153,0.3); background: rgba(52,211,153,0.08); color: #6EE7B7;
}
.cart-alert--info {
    border: 1px solid rgba(56,189,248,0.3); background: rgba(56,189,248,0.08); color: #7DD3FC;
}
.cart-alert--warn {
    border: 1px solid rgba(220,38,38,0.3); background: rgba(220,38,38,0.08); color: #FCA5A5;
}

.cart-empty-state {
    text-align: center;
    padding: 4.5rem 1.5rem;
    border: 1px dashed var(--color-border);
    border-radius: 1.25rem;
    background: rgba(255,255,255,0.015);
    max-width: 36rem;
    margin: 0 auto;
}
.cart-empty-icon {
    width: 4.5rem; height: 4.5rem; margin: 0 auto 1.5rem;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%;
    color: var(--color-muted);
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--color-border);
}
.cart-empty-title {
    font-size: 1.4rem; font-weight: 800; color: #F5F5F5; margin: 0 0 0.5rem;
}
.cart-empty-desc {
    font-size: 0.95rem; color: var(--color-muted); line-height: 1.6; margin: 0;
}

.cart-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);
    gap: 2.5rem;
    align-items: start;
}

.cart-items-panel {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
.cart-table-header {
    display: grid;
    grid-template-columns: minmax(0, 2.5fr) 1.2fr 1.2fr 1.3fr 40px;
    padding: 0.75rem 1.25rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--color-muted);
    border-bottom: 1px solid var(--color-border);
}

.cart-items-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.cart-item-row {
    display: grid;
    grid-template-columns: minmax(0, 2.5fr) 1.2fr 1.2fr 1.3fr 40px;
    align-items: center;
    padding: 1.25rem;
    border: 1px solid var(--color-border);
    border-radius: 1rem;
    background: rgba(255,255,255,0.025);
    transition: border-color 0.2s;
}
.cart-item-row:hover { border-color: rgba(220,38,38,0.3); }

.cart-item-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.cart-item-img {
    width: 60px; height: 60px; border-radius: 0.65rem; object-fit: cover; flex-shrink: 0;
    border: 1px solid var(--color-border);
}
.cart-item-img--placeholder {
    display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.03);
}
.cart-item-details { display: flex; flex-direction: column; gap: 0.35rem; }
.cart-item-name {
    font-size: 0.95rem; font-weight: 700; color: #F5F5F5; text-decoration: none; line-height: 1.35;
}
.cart-item-name:hover { color: var(--rb-red); }
.cart-item-type-badge {
    font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
    padding: 0.15rem 0.5rem; border-radius: 999px; width: fit-content;
}
.cart-item-type-badge--barang {
    color: #FBBF24; background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.25);
}
.cart-item-type-badge--jasa {
    color: #38BDF8; background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.25);
}

.cart-item-price { font-size: 0.9rem; color: var(--color-muted); }
.cart-item-subtotal { font-size: 1rem; font-weight: 800; color: #F5F5F5; text-align: right; }

.cart-qty-form { margin: 0; }
.cart-qty-control {
    display: inline-flex; align-items: center;
    border: 1px solid var(--color-border); border-radius: 0.5rem; overflow: hidden;
    background: rgba(255,255,255,0.03);
}
.cart-qty-btn {
    background: none; border: none; color: var(--color-text); width: 28px; height: 32px;
    cursor: pointer; font-size: 1rem; display: flex; align-items: center; justify-content: center;
    transition: background 0.2s;
}
.cart-qty-btn:hover { background: rgba(255,255,255,0.1); }
.cart-qty-input {
    width: 38px; height: 32px; text-align: center; background: none; border: none;
    color: #F5F5F5; font-size: 0.9rem; font-weight: 700; -moz-appearance: textfield;
}
.cart-qty-input::-webkit-outer-spin-button,
.cart-qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

.cart-remove-btn {
    background: none; border: none; color: var(--color-muted); cursor: pointer; padding: 0.4rem;
    border-radius: 0.4rem; display: flex; align-items: center; justify-content: center;
    transition: color 0.2s, background 0.2s;
}
.cart-remove-btn:hover { color: #EF4444; background: rgba(239,68,68,0.1); }

.cart-footer-actions {
    display: flex; justify-content: space-between; align-items: center; margin-top: 0.75rem;
}
.cart-back-link { font-size: 0.85rem; color: var(--color-muted); text-decoration: none; font-weight: 600; }
.cart-back-link:hover { color: var(--rb-red); }
.cart-clear-btn {
    background: none; border: 1px solid var(--color-border); color: var(--color-muted);
    font-size: 0.78rem; padding: 0.4rem 0.85rem; border-radius: 0.5rem; cursor: pointer;
    transition: all 0.2s;
}
.cart-clear-btn:hover { border-color: #EF4444; color: #EF4444; }

/* ── Summary ── */
.cart-summary-panel {
    border: 1px solid var(--color-border);
    border-radius: 1.25rem;
    background: rgba(255,255,255,0.025);
    padding: 1.75rem;
    display: flex; flex-direction: column; gap: 1.25rem;
    position: sticky; top: 6.5rem;
}
.cart-summary-title {
    font-size: 1.25rem; font-weight: 800; color: #F5F5F5; margin: 0; border-bottom: 1px solid var(--color-border);
    padding-bottom: 0.85rem;
}
.cart-summary-row {
    display: flex; justify-content: space-between; align-items: baseline; gap: 1rem;
    font-size: 0.9rem; color: var(--color-muted);
}
.cart-summary-row strong { color: #F5F5F5; font-size: 0.95rem; }
.cart-summary-row--note { font-size: 0.8rem; }
.cart-summary-row--total {
    padding-top: 1rem; border-top: 1px solid var(--color-border);
    font-size: 1.05rem; color: #F5F5F5; font-weight: 700;
}
.cart-summary-total {
    font-size: 1.4rem !important; font-weight: 900 !important; color: #F5F5F5 !important;
}

.cart-checkout-btn {
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    padding: 0.95rem 1.5rem; border-radius: 999px; font-size: 0.95rem; font-weight: 800;
    text-decoration: none; margin-top: 0.5rem;
}

.cart-trust-badges {
    display: flex; flex-direction: column; gap: 0.65rem;
    padding-top: 1rem; border-top: 1px solid var(--color-border);
}
.cart-trust-badge {
    display: flex; align-items: center; gap: 0.6rem; font-size: 0.8rem; color: var(--color-muted);
}

@media (max-width: 900px) {
    .cart-layout { grid-template-columns: 1fr; }
    .cart-summary-panel { position: static; }
}

@media (max-width: 640px) {
    .cart-table-header { display: none; }
    .cart-item-row {
        grid-template-columns: 1fr; gap: 1rem;
    }
    .cart-item-subtotal { text-align: left; }
    .cart-item-actions { justify-self: end; }
}
</style>

<script>
function updateQty(btn, delta) {
    const input = btn.parentElement.querySelector('.cart-qty-input');
    if (!input) return;
    let current = parseInt(input.value) || 1;
    let max = parseInt(input.max) || 999;
    let next = current + delta;
    if (next < 1) next = 1;
    if (next > max) next = max;
    if (next !== current) {
        input.value = next;
        input.form.submit();
    }
}
</script>
@endsection
