@extends('layouts.public')

@php
    use App\Support\ProductType;

    $name    = $product->translate('name', $locale);
    $summary = $product->translate('short_description', $locale);
@endphp

@section('meta_title',       'Pesan — ' . $name)
@section('meta_description', 'Formulir pemesanan ' . $name . ' di RBeverything.')
{{-- An order form has nothing to offer a search engine, and indexing it would
     scatter duplicate thin pages across the catalogue. --}}
@section('meta_robots',      'noindex, nofollow')

@section('content')

    <section aria-label="Formulir pemesanan">
        <div class="rb-section" style="padding-bottom:5rem;">

            <nav class="catalog-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="catalog-breadcrumb__link">Home</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                <a href="{{ route('products.index') }}" class="catalog-breadcrumb__link">Produk &amp; Layanan</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                <a href="{{ route('products.show', $product->slug) }}" class="catalog-breadcrumb__link">{{ Str::limit($name, 30) }}</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                <span class="catalog-breadcrumb__current">Pesan</span>
            </nav>

            <span class="rb-section-label">Pemesanan</span>
            <h1 class="order-title">Pesan {{ $name }}</h1>
            <p class="order-lead">
                Isi data di bawah ini. Pesanan langsung masuk ke kami dan kamu akan menerima nomor pesanan
                untuk dipakai saat menghubungi kami.
            </p>

            <div class="order-layout">

                {{-- ── The form ──────────────────────────────────── --}}
                <form method="POST" action="{{ route('order.store', $product->slug) }}" class="order-form">
                    @csrf

                    @if($errors->any())
                        <div class="order-alert order-alert--error" role="alert">
                            <strong>Ada {{ $errors->count() }} isian yang perlu diperbaiki:</strong>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="order-field">
                        <label for="customer_name">Nama lengkap <span class="order-req">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" required maxlength="120"
                               value="{{ old('customer_name') }}" autocomplete="name">
                    </div>

                    <div class="order-row">
                        <div class="order-field">
                            <label for="customer_email">Email <span class="order-req">*</span></label>
                            <input type="email" id="customer_email" name="customer_email" required maxlength="190"
                                   value="{{ old('customer_email') }}" autocomplete="email">
                            <small>Konfirmasi pesanan dikirim ke alamat ini.</small>
                        </div>

                        <div class="order-field">
                            <label for="customer_phone">Nomor WhatsApp <span class="order-req">*</span></label>
                            <input type="tel" id="customer_phone" name="customer_phone" required maxlength="40"
                                   value="{{ old('customer_phone') }}" autocomplete="tel"
                                   placeholder="0812 3456 7890">
                            <small>Kami pakai nomor ini untuk konfirmasi{{ $product->isBarang() ? ' dan menghitung ongkos kirim' : '' }}.</small>
                        </div>
                    </div>

                    <div class="order-field order-field--narrow">
                        <label for="qty">Jumlah <span class="order-req">*</span></label>
                        <input type="number" id="qty" name="qty" required min="1"
                               max="{{ $product->tracksStock() ? $product->stock : 999 }}"
                               value="{{ old('qty', 1) }}">
                        @if($product->tracksStock())
                            <small>Stok tersedia: {{ $product->stock }}</small>
                        @endif
                    </div>

                    @if($product->isBarang())
                        <div class="order-field">
                            <label for="shipping_address">Alamat pengiriman <span class="order-req">*</span></label>
                            <textarea id="shipping_address" name="shipping_address" rows="4" required
                                      placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan, kota, kode pos">{{ old('shipping_address') }}</textarea>
                            <small>Ongkos kirim dihitung manual dan kami konfirmasikan lewat WhatsApp setelah pesanan masuk.</small>
                        </div>
                    @else
                        <div class="order-field order-field--narrow">
                            <label for="preferred_date">Tanggal yang diinginkan</label>
                            <input type="date" id="preferred_date" name="preferred_date"
                                   min="{{ now()->toDateString() }}" value="{{ old('preferred_date') }}">
                            <small>Opsional — kami konfirmasikan ketersediaan jadwalnya.</small>
                        </div>
                    @endif

                    <div class="order-field">
                        <label for="notes">Catatan tambahan</label>
                        <textarea id="notes" name="notes" rows="3"
                                  placeholder="Spesifikasi khusus, pertanyaan, atau apa pun yang perlu kami tahu">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Honeypot: hidden from people, irresistible to bots. Anything
                         typed here fails validation (see StoreOrderRequest). --}}
                    <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <button type="submit" class="rb-btn-primary order-submit">
                        Kirim Pesanan
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <p class="order-disclaimer">
                        Mengirim formulir ini belum berarti pembayaran. Kami konfirmasi dulu ketersediaan
                        {{ $product->isBarang() ? 'dan ongkos kirim' : 'dan jadwal' }}, baru kamu bayar.
                    </p>
                </form>

                {{-- ── Order summary ─────────────────────────────── --}}
                <aside class="order-summary" aria-label="Ringkasan pesanan">
                    @if($product->thumbnail)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($product->thumbnail) }}"
                             alt="{{ $name }}" class="order-summary__img">
                    @endif

                    <div class="product-card__badges">
                        <span class="product-badge product-badge--{{ $product->type }}">
                            {{ ProductType::label($product->type) }}
                        </span>
                    </div>

                    <h2 class="order-summary__title">{{ $name }}</h2>

                    @if($summary !== '')
                        <p class="order-summary__desc">{{ $summary }}</p>
                    @endif

                    <div class="order-summary__price-row">
                        <span>Harga satuan</span>
                        <strong>{{ $product->formattedPrice() }}</strong>
                    </div>

                    @if($product->isBarang())
                        <div class="order-summary__price-row order-summary__price-row--muted">
                            <span>Ongkos kirim</span>
                            <span>dihitung manual</span>
                        </div>
                    @endif

                    <a href="{{ route('products.show', $product->slug) }}" class="order-summary__back">
                        &larr; Kembali ke detail produk
                    </a>
                </aside>
            </div>
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

.order-title {
    font-size: clamp(1.75rem, 4vw, 2.75rem);
    font-weight: 900; letter-spacing: -0.025em; line-height: 1.15;
    color: #F5F5F5; margin: 0 0 0.75rem;
}
.order-lead {
    font-size: 1rem; color: var(--color-muted); line-height: 1.75;
    max-width: 34rem; margin: 0 0 2.5rem;
}

.order-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
    gap: 2.5rem;
    align-items: start;
}

/* ── Form ───────────────────────────────────────────── */
.order-form { display: flex; flex-direction: column; gap: 1.4rem; }

.order-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.4rem; }

.order-field { display: flex; flex-direction: column; gap: 0.45rem; }
.order-field--narrow { max-width: 14rem; }

.order-field label {
    font-size: 0.82rem; font-weight: 700; color: var(--color-text);
    letter-spacing: 0.01em;
}
.order-req { color: var(--rb-red); }

.order-field input,
.order-field textarea {
    width: 100%;
    padding: 0.7rem 0.9rem;
    border-radius: 0.65rem;
    border: 1px solid var(--color-border);
    background: rgba(255,255,255,0.03);
    color: var(--color-text);
    font-size: 0.9rem;
    font-family: inherit;
    transition: border-color 0.3s ease, background 0.3s ease;
}
.order-field input:focus,
.order-field textarea:focus {
    outline: none;
    border-color: var(--rb-red-border);
    background: rgba(255,255,255,0.05);
}
.order-field textarea { resize: vertical; line-height: 1.6; }
.order-field small { font-size: 0.75rem; color: var(--color-muted); line-height: 1.5; }

.order-alert {
    padding: 1rem 1.1rem;
    border-radius: 0.75rem;
    font-size: 0.85rem;
    line-height: 1.6;
}
.order-alert--error {
    border: 1px solid rgba(220,38,38,0.3);
    background: rgba(220,38,38,0.07);
    color: #FCA5A5;
}
.order-alert ul { margin: 0.5rem 0 0 1.1rem; }

.order-submit {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
    padding: 0.85rem 1.6rem; border-radius: 999px;
    font-size: 0.9rem; font-weight: 700; cursor: pointer;
    width: fit-content;
}

.order-disclaimer { font-size: 0.78rem; color: var(--color-muted); line-height: 1.6; margin: 0; }

/* ── Summary ────────────────────────────────────────── */
.order-summary {
    display: flex; flex-direction: column; gap: 0.85rem;
    padding: 1.5rem;
    border: 1px solid var(--color-border);
    border-radius: 1rem;
    background: rgba(255,255,255,0.025);
    position: sticky; top: 6.5rem;
}
.order-summary__img {
    width: 100%; aspect-ratio: 4/3; object-fit: cover;
    border-radius: 0.75rem; display: block;
}
.order-summary__title { font-size: 1.1rem; font-weight: 800; color: #F5F5F5; margin: 0; line-height: 1.35; }
.order-summary__desc { font-size: 0.85rem; color: var(--color-muted); line-height: 1.6; margin: 0; }
.order-summary__price-row {
    display: flex; justify-content: space-between; align-items: baseline; gap: 1rem;
    padding-top: 0.85rem; border-top: 1px solid var(--color-border);
    font-size: 0.85rem; color: var(--color-text);
}
.order-summary__price-row strong { font-size: 1.05rem; font-weight: 800; color: #F5F5F5; }
.order-summary__price-row--muted { color: var(--color-muted); border-top: none; padding-top: 0; }
.order-summary__back {
    font-size: 0.8rem; color: var(--color-muted); text-decoration: none;
    margin-top: 0.35rem; transition: color 0.25s ease;
}
.order-summary__back:hover { color: var(--rb-red); }

.product-card__badges { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.product-badge {
    font-size: 0.65rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;
    border-radius: 999px; padding: 0.2rem 0.6rem; border: 1px solid transparent;
}
.product-badge--barang { color: #FBBF24; background: rgba(251,191,36,0.08); border-color: rgba(251,191,36,0.25); }
.product-badge--jasa   { color: #38BDF8; background: rgba(56,189,248,0.08); border-color: rgba(56,189,248,0.25); }

@media (max-width: 900px) {
    .order-layout { grid-template-columns: 1fr; }
    .order-summary { position: static; order: -1; }
}
@media (max-width: 560px) {
    .order-row { grid-template-columns: 1fr; }
}
</style>
@endsection
