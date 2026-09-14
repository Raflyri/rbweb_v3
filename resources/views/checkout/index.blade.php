@extends('layouts.public')

@section('meta_title', 'Checkout Pembayaran — RBeverything')
@section('meta_description', 'Selesaikan pesanan belanja Anda di RBeverything. Pilih metode pembayaran QRIS, Virtual Account, atau Transfer Bank Manual.')
@section('meta_robots', 'noindex, nofollow')

@section('content')
<section class="checkout-section" aria-label="Checkout Pembayaran">
    <div class="rb-section" style="padding-bottom:5rem;">

        <nav class="catalog-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="catalog-breadcrumb__link">{{ __('nav.home') }}</a>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
            <a href="{{ route('cart.index') }}" class="catalog-breadcrumb__link">Keranjang Belanja</a>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
            <span class="catalog-breadcrumb__current">Checkout Pembayaran</span>
        </nav>

        <span class="rb-section-label">Pembayaran Aman</span>
        <h1 class="checkout-title">Checkout Pembayaran</h1>
        <p class="checkout-lead">Lengkapi data diri dan pilih metode pembayaran favorit Anda untuk menyelesaikan transaksi.</p>

        @if($errors->any())
            <div class="checkout-alert checkout-alert--error" role="alert">
                <strong>Mohon periksa kembali form berikut ({{ $errors->count() }} kesalahan):</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}" class="checkout-layout" id="checkout-form">
            @csrf

            {{-- ── Left: Form Inputs & Payment Selection ── --}}
            <div class="checkout-main">

                {{-- 1. Data Pelanggan --}}
                <div class="checkout-card">
                    <h2 class="checkout-card__title">
                        <span class="checkout-step">1</span>
                        Informasi Pembeli
                    </h2>

                    <div class="checkout-form-grid">
                        <div class="order-field">
                            <label for="customer_name">Nama Lengkap <span class="order-req">*</span></label>
                            <input type="text" id="customer_name" name="customer_name" required maxlength="120"
                                   value="{{ old('customer_name') }}" autocomplete="name" placeholder="Nama lengkap Anda">
                        </div>

                        <div class="order-row">
                            <div class="order-field">
                                <label for="customer_email">Alamat Email <span class="order-req">*</span></label>
                                <input type="email" id="customer_email" name="customer_email" required maxlength="190"
                                       value="{{ old('customer_email') }}" autocomplete="email" placeholder="nama@email.com">
                                <small>Konfirmasi pesanan dan tanda terima dikirim ke email ini.</small>
                            </div>

                            <div class="order-field">
                                <label for="customer_phone">Nomor WhatsApp / HP <span class="order-req">*</span></label>
                                <input type="tel" id="customer_phone" name="customer_phone" required maxlength="40"
                                       value="{{ old('customer_phone') }}" autocomplete="tel" placeholder="0812 3456 7890">
                                <small>Untuk kemudahan konfirmasi & pelacakan status.</small>
                            </div>
                        </div>

                        @if($hasPhysicalItems)
                            <div class="order-field">
                                <label for="shipping_address">Alamat Pengiriman Lengkap <span class="order-req">*</span></label>
                                <textarea id="shipping_address" name="shipping_address" rows="3" required
                                          placeholder="Alamat jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota/kabupaten, kode pos...">{{ old('shipping_address') }}</textarea>
                                <small>Pengiriman produk fisik menggunakan ekspedisi rekanan terpercaya.</small>
                            </div>
                        @endif

                        <div class="order-field">
                            <label for="notes">Catatan Tambahan (Opsional)</label>
                            <textarea id="notes" name="notes" rows="2"
                                      placeholder="Catatan khusus untuk pengiriman atau permintaan spesifik...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 2. Metode Pembayaran --}}
                <div class="checkout-card">
                    <h2 class="checkout-card__title">
                        <span class="checkout-step">2</span>
                        Pilih Metode Pembayaran
                    </h2>
                    <p class="checkout-card__subtitle">Pilih saluran pembayaran resmi yang Anda inginkan:</p>

                    <div class="payment-methods-grid">
                        @php
                            $defaultSelected = old('payment_method', array_key_first($paymentMethods));
                        @endphp

                        @foreach($paymentMethods as $channelKey => $method)
                            <label class="payment-method-card {{ $defaultSelected === $channelKey ? 'payment-method-card--active' : '' }}">
                                <input type="radio" name="payment_method" value="{{ $channelKey }}"
                                       class="payment-method-radio"
                                       {{ $defaultSelected === $channelKey ? 'checked' : '' }}
                                       onchange="highlightPaymentMethod(this)">

                                <div class="payment-method-body">
                                    <div class="payment-method-header">
                                        <div class="payment-method-title-wrap">
                                            @if($method['category'] === 'qris')
                                                <span class="payment-icon">📱</span>
                                            @elseif($method['category'] === 'ewallet')
                                                <span class="payment-icon">📲</span>
                                            @elseif(in_array($method['category'], ['va', 'bill'], true))
                                                <span class="payment-icon">🏦</span>
                                            @elseif($method['category'] === 'cstore')
                                                <span class="payment-icon">🏪</span>
                                            @else
                                                <span class="payment-icon">💳</span>
                                            @endif
                                            <strong class="payment-method-name">{{ $method['name'] }}</strong>
                                        </div>

                                        @if(!empty($method['badge']))
                                            <span class="payment-method-badge">{{ $method['badge'] }}</span>
                                        @endif
                                    </div>

                                    <p class="payment-method-desc">{{ $method['description'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Bot Honeypot --}}
                <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

            </div>

            {{-- ── Right: Summary & Submit ── --}}
            <aside class="checkout-sidebar">
                <div class="cart-summary-panel">
                    <h2 class="cart-summary-title">Rincian Pesanan</h2>

                    <div class="checkout-items-preview">
                        @foreach($items as $item)
                            <div class="checkout-item-preview-row">
                                <div class="checkout-item-preview-name">
                                    <span>{{ $item['name'] }}</span>
                                    <small>&times; {{ $item['qty'] }}</small>
                                </div>
                                <strong class="checkout-item-preview-subtotal">{{ $item['formatted_subtotal'] }}</strong>
                            </div>
                        @endforeach
                    </div>

                    <div class="cart-summary-row" style="padding-top:0.75rem; border-top:1px solid var(--color-border);">
                        <span>Total Kuantitas</span>
                        <strong>{{ $count }} item</strong>
                    </div>

                    <div class="cart-summary-row">
                        <span>Subtotal Produk</span>
                        <strong class="cart-summary-amount">{{ $subtotal }}</strong>
                    </div>

                    @if($hasPhysicalItems)
                        <div class="cart-summary-row cart-summary-row--note">
                            <span>Ongkos Kirim</span>
                            <span>Dikonfirmasi admin</span>
                        </div>
                    @endif

                    <div class="cart-summary-row cart-summary-row--total">
                        <span>Total Bayar</span>
                        <strong class="cart-summary-total">{{ $subtotal }}</strong>
                    </div>

                    <button type="submit" class="rb-btn-primary cart-checkout-btn" style="width:100%; cursor:pointer;">
                        Proses Pembayaran Sekarang
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <p class="checkout-security-note">
                        🔒 Data transaksi Anda dienkripsi dengan standar keamanan industri dan diproses langsung oleh Midtrans Core API resmi.
                    </p>
                </div>
            </aside>

        </form>

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

.checkout-title {
    font-size: clamp(1.75rem, 4vw, 2.75rem);
    font-weight: 900; letter-spacing: -0.025em; line-height: 1.15;
    color: #F5F5F5; margin: 0 0 0.75rem;
}
.checkout-lead {
    font-size: 1rem; color: var(--color-muted); line-height: 1.75;
    max-width: 36rem; margin: 0 0 2.5rem;
}

.checkout-alert {
    padding: 1rem 1.25rem; border-radius: 0.75rem; font-size: 0.875rem; margin-bottom: 2rem;
}
.checkout-alert--error {
    border: 1px solid rgba(220,38,38,0.3); background: rgba(220,38,38,0.08); color: #FCA5A5;
}
.checkout-alert ul { margin: 0.5rem 0 0 1.2rem; }

.checkout-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr);
    gap: 2.5rem;
    align-items: start;
}

.checkout-main {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.checkout-card {
    border: 1px solid var(--color-border);
    border-radius: 1.25rem;
    background: rgba(255,255,255,0.025);
    padding: 1.75rem;
}

.checkout-card__title {
    font-size: 1.2rem; font-weight: 800; color: #F5F5F5; margin: 0 0 1.25rem;
    display: flex; align-items: center; gap: 0.75rem;
}
.checkout-step {
    width: 28px; height: 28px; border-radius: 50%; background: var(--rb-red);
    color: #FFFFFF; font-size: 0.85rem; font-weight: 900; display: inline-flex;
    align-items: center; justify-content: center; flex-shrink: 0;
}
.checkout-card__subtitle {
    font-size: 0.875rem; color: var(--color-muted); margin: -0.5rem 0 1.25rem;
}

.checkout-form-grid {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.order-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
.order-field { display: flex; flex-direction: column; gap: 0.45rem; }
.order-field label {
    font-size: 0.82rem; font-weight: 700; color: var(--color-text);
}
.order-req { color: var(--rb-red); }

.order-field input,
.order-field textarea {
    width: 100%;
    padding: 0.75rem 0.95rem;
    border-radius: 0.65rem;
    border: 1px solid var(--color-border);
    background: rgba(255,255,255,0.03);
    color: var(--color-text);
    font-size: 0.9rem;
    font-family: inherit;
    transition: border-color 0.25s, background 0.25s;
}
.order-field input:focus,
.order-field textarea:focus {
    outline: none;
    border-color: var(--rb-red);
    background: rgba(255,255,255,0.05);
}
.order-field small { font-size: 0.75rem; color: var(--color-muted); line-height: 1.4; }

/* ── Payment Methods Grid ── */
.payment-methods-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.85rem;
}

.payment-method-card {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.15rem;
    border: 1px solid var(--color-border);
    border-radius: 0.85rem;
    background: rgba(255,255,255,0.02);
    cursor: pointer;
    transition: all 0.2s ease;
}
.payment-method-card:hover {
    border-color: rgba(220,38,38,0.4);
    background: rgba(255,255,255,0.035);
}
.payment-method-card--active {
    border-color: var(--rb-red) !important;
    background: rgba(220,38,38,0.06) !important;
    box-shadow: 0 0 12px rgba(220,38,38,0.15);
}

.payment-method-radio {
    margin-top: 0.25rem;
    accent-color: var(--rb-red);
    cursor: pointer;
    width: 18px; height: 18px;
}

.payment-method-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.payment-method-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
}
.payment-method-title-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.payment-icon { font-size: 1.1rem; }
.payment-method-name {
    font-size: 0.95rem; font-weight: 700; color: #F5F5F5;
}
.payment-method-badge {
    font-size: 0.65rem; font-weight: 700; padding: 0.15rem 0.5rem;
    border-radius: 999px; background: rgba(52,211,153,0.12);
    border: 1px solid rgba(52,211,153,0.3); color: #34D399;
}
.payment-method-desc {
    font-size: 0.8rem; color: var(--color-muted); line-height: 1.45; margin: 0;
}

/* ── Sidebar ── */
.checkout-sidebar {
    position: sticky; top: 6.5rem;
}
.checkout-items-preview {
    display: flex; flex-direction: column; gap: 0.65rem;
}
.checkout-item-preview-row {
    display: flex; justify-content: space-between; align-items: baseline; gap: 1rem;
    font-size: 0.85rem;
}
.checkout-item-preview-name {
    color: var(--color-text); line-height: 1.4;
}
.checkout-item-preview-name small {
    color: var(--color-muted); font-weight: 600; margin-left: 0.25rem;
}
.checkout-item-preview-subtotal {
    color: #F5F5F5; font-size: 0.9rem; flex-shrink: 0;
}

.checkout-security-note {
    font-size: 0.75rem; color: var(--color-muted); line-height: 1.5; text-align: center; margin: 0;
}

@media (max-width: 900px) {
    .checkout-layout { grid-template-columns: 1fr; }
    .checkout-sidebar { position: static; }
}
@media (max-width: 560px) {
    .order-row { grid-template-columns: 1fr; }
}
</style>

<script>
function highlightPaymentMethod(radio) {
    document.querySelectorAll('.payment-method-card').forEach(function(card) {
        card.classList.remove('payment-method-card--active');
    });
    if (radio.checked) {
        radio.closest('.payment-method-card').classList.add('payment-method-card--active');
    }
}
</script>
@endsection
