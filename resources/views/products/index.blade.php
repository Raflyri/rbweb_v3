@extends('layouts.public')

{{-- ════════════════════════════════════════════════════════════
     SEO — Catalogue index
════════════════════════════════════════════════════════════ --}}
@section('meta_title',       'Produk & Layanan')
@section('meta_description', 'Katalog produk dan layanan RBeverything — perangkat, instalasi, dan jasa teknologi untuk kebutuhan rumah maupun usaha.')
@section('og_type',          'website')
@section('og_title',         'Produk & Layanan — RBeverything')
@section('og_description',   'Katalog produk dan layanan RBeverything — perangkat, instalasi, dan jasa teknologi untuk kebutuhan rumah maupun usaha.')
@section('canonical',        route('products.index'))

{{-- Light up whichever nav item brought the visitor here. --}}
@if(request()->query('type') === 'jasa')
    @section('nav_services_active', 'style="color:var(--color-text);"')
@else
    @section('nav_products_active', 'style="color:var(--color-text);"')
@endif

@php
    use App\Support\ProductType;

    $tabs = [
        ['key' => null,                 'label' => 'Semua', 'count' => $counts['all']],
        ['key' => ProductType::BARANG,  'label' => 'Barang', 'count' => $counts[ProductType::BARANG]],
        ['key' => ProductType::JASA,    'label' => 'Jasa',   'count' => $counts[ProductType::JASA]],
    ];
@endphp

@section('content')

    {{-- ════════════════════════════════════════════════════════
         HERO
    ════════════════════════════════════════════════════════ --}}
    <section class="catalog-hero" aria-label="Produk & Layanan">
        <div class="rb-section" style="padding-bottom:2.5rem;">

            <nav class="catalog-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="catalog-breadcrumb__link">Home</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                <span class="catalog-breadcrumb__current">Produk &amp; Layanan</span>
            </nav>

            <div class="catalog-hero__inner">
                <div>
                    <span class="rb-section-label">Katalog</span>
                    <h1 class="rb-section-title" style="margin-bottom:0.75rem;">Produk &amp; Layanan</h1>
                    <p class="catalog-hero__subtitle">
                        Perangkat, instalasi, dan jasa teknologi yang kami kerjakan sendiri — untuk kebutuhan
                        rumah maupun usaha.
                    </p>
                </div>
            </div>

            {{-- ── Filter tabs ─────────────────────────────────── --}}
            <div class="catalog-tabs" role="tablist" aria-label="Saring berdasarkan jenis">
                @foreach($tabs as $tab)
                    @php
                        $isActive = $type === $tab['key'];
                        $url = $tab['key']
                            ? route('products.index', ['type' => $tab['key']])
                            : route('products.index');
                    @endphp
                    <a href="{{ $url }}"
                       class="catalog-tab {{ $isActive ? 'catalog-tab--active' : '' }}"
                       role="tab"
                       aria-selected="{{ $isActive ? 'true' : 'false' }}">
                        {{ $tab['label'] }}
                        <span class="catalog-tab__count">{{ $tab['count'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <div class="rb-divider"></div>

    {{-- ════════════════════════════════════════════════════════
         GRID
    ════════════════════════════════════════════════════════ --}}
    <section aria-label="Daftar produk dan layanan" class="catalog-list">
        <div class="rb-section" style="padding-top:3.5rem;padding-bottom:5rem;">

            @if($products->count() > 0)

                <div class="catalog-grid">
                    @foreach($products as $product)
                        @php
                            $name    = $product->translate('name', $locale);
                            $summary = $product->translate('short_description', $locale);
                            $price   = $product->formattedPrice();
                            $url     = route('products.show', $product->slug);
                        @endphp

                        <article class="product-card" id="product-card-{{ $product->id }}">

                            <a href="{{ $url }}" class="product-card__cover-link" aria-label="{{ $name }}" tabindex="-1">
                                @if($product->thumbnail)
                                    <div class="product-card__cover">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($product->thumbnail) }}"
                                             alt="{{ $name }}" class="product-card__img" loading="lazy">
                                    </div>
                                @else
                                    <div class="product-card__cover product-card__cover--placeholder">
                                        <svg width="44" height="44" viewBox="0 0 24 24" fill="none"
                                             stroke="rgba(220,38,38,0.25)" stroke-width="1"
                                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                                            <path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                                        </svg>
                                    </div>
                                @endif

                                @if($product->is_featured)
                                    <span class="product-card__ribbon">Unggulan</span>
                                @endif
                            </a>

                            <div class="product-card__body">

                                <div class="product-card__badges">
                                    <span class="product-badge product-badge--{{ $product->type }}">
                                        {{ ProductType::label($product->type) }}
                                    </span>
                                    @if(! $product->isInStock())
                                        <span class="product-badge product-badge--empty">Stok habis</span>
                                    @endif
                                </div>

                                <h2 class="product-card__title">
                                    <a href="{{ $url }}" class="product-card__title-link">{{ $name }}</a>
                                </h2>

                                @if($summary !== '')
                                    <p class="product-card__excerpt">{{ $summary }}</p>
                                @endif

                                <div class="product-card__footer">
                                    @if($price)
                                        <span class="product-card__price">{{ $price }}</span>
                                    @else
                                        {{-- No price is a deliberate state, not missing data: these
                                             items are quoted after a conversation. --}}
                                        <span class="product-card__price product-card__price--ask">Hubungi Kami</span>
                                    @endif

                                    <a href="{{ $url }}" class="product-card__cta" aria-label="Lihat detail {{ $name }}">
                                        Lihat Detail
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M5 12h14M12 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($products->hasPages())
                    <div class="catalog-pagination" aria-label="Pagination">
                        {{ $products->links() }}
                    </div>
                @endif

            @else
                {{-- ════════════════════════════════════════════
                     EMPTY STATE
                ════════════════════════════════════════════ --}}
                <div class="catalog-empty" role="status">
                    <div class="catalog-empty__icon-wrap">
                        <svg class="catalog-empty__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                            <path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                        </svg>
                    </div>

                    @if($type)
                        <h3 class="catalog-empty__title">Belum ada {{ ProductType::label($type) }} yang tayang</h3>
                        <p class="catalog-empty__desc">
                            Kategori ini sedang kosong. Lihat seluruh katalog, atau hubungi kami langsung untuk
                            kebutuhan yang belum terdaftar di sini.
                        </p>
                        <a href="{{ route('products.index') }}" class="catalog-empty__cta">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                            </svg>
                            Lihat semua
                        </a>
                    @else
                        <h3 class="catalog-empty__title">Katalog sedang disiapkan</h3>
                        <p class="catalog-empty__desc">
                            Daftar produk dan layanan kami sedang dirapikan. Sementara ini, ceritakan saja
                            kebutuhanmu — kami bantu carikan bentuk yang paling pas.
                        </p>
                        <a href="mailto:{{ config('mail.from.address', 'hello@rbeverything.com') }}" class="catalog-empty__cta">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>
                            </svg>
                            Hubungi Kami
                        </a>
                    @endif
                </div>
            @endif

        </div>
    </section>

@endsection

{{-- ════════════════════════════════════════════════════════════
     PAGE-SPECIFIC STYLES
════════════════════════════════════════════════════════════ --}}
@section('styles')
<style>
/* ══════════════════════════════════════════════════════
   BREADCRUMB
══════════════════════════════════════════════════════ */
.catalog-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: var(--color-muted);
    margin-bottom: 2rem;
}
.catalog-breadcrumb__link {
    color: var(--color-muted);
    text-decoration: none;
    transition: color 0.25s ease;
}
.catalog-breadcrumb__link:hover { color: var(--color-text); }
.catalog-breadcrumb__current { color: var(--color-text); font-weight: 500; }

/* ══════════════════════════════════════════════════════
   HERO
══════════════════════════════════════════════════════ */
.catalog-hero__inner { margin-top: 2rem; }

.catalog-hero__subtitle {
    font-size: 1.05rem;
    color: var(--color-muted);
    font-weight: 300;
    max-width: 34rem;
    line-height: 1.75;
    margin: 0;
}

/* ══════════════════════════════════════════════════════
   FILTER TABS
══════════════════════════════════════════════════════ */
.catalog-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 2.5rem;
}

.catalog-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 1.1rem;
    border: 1px solid var(--color-border);
    border-radius: 999px;
    background: rgba(255,255,255,0.02);
    color: var(--color-muted);
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: border-color 0.3s ease, color 0.3s ease, background 0.3s ease;
}
.catalog-tab:hover {
    color: var(--color-text);
    border-color: rgba(220,38,38,0.2);
}
.catalog-tab--active {
    color: #F5F5F5;
    border-color: var(--rb-red-border);
    background: rgba(185,28,28,0.07);
}
.catalog-tab__count {
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--color-muted);
    background: rgba(255,255,255,0.05);
    border-radius: 999px;
    padding: 0.1rem 0.45rem;
}
.catalog-tab--active .catalog-tab__count {
    color: var(--rb-red);
    background: rgba(220,38,38,0.1);
}

/* ══════════════════════════════════════════════════════
   GRID + CARD
══════════════════════════════════════════════════════ */
.catalog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(17rem, 1fr));
    gap: 1.75rem;
}

.product-card {
    position: relative;
    display: flex;
    flex-direction: column;
    background: rgba(255,255,255,0.025);
    border: 1px solid var(--color-border);
    border-radius: 1rem;
    overflow: hidden;
    transition: border-color 0.4s ease, box-shadow 0.4s ease, transform 0.35s ease;
}
.product-card:hover {
    border-color: var(--rb-red-border);
    box-shadow: 0 0 18px rgba(185,28,28,0.12);
    transform: translateY(-3px);
}

.product-card__cover-link { position: relative; display: block; }

.product-card__cover {
    aspect-ratio: 4 / 3;
    overflow: hidden;
    background: rgba(255,255,255,0.02);
}
.product-card__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.6s ease;
}
.product-card:hover .product-card__img { transform: scale(1.04); }

.product-card__cover--placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-card__ribbon {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #F5F5F5;
    background: rgba(185,28,28,0.85);
    border-radius: 999px;
    padding: 0.2rem 0.6rem;
}

.product-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1.25rem;
    flex: 1;
}

.product-card__badges { display: flex; flex-wrap: wrap; gap: 0.4rem; }

.product-badge {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    border-radius: 999px;
    padding: 0.2rem 0.6rem;
    border: 1px solid transparent;
}
.product-badge--barang {
    color: #FBBF24;
    background: rgba(251,191,36,0.08);
    border-color: rgba(251,191,36,0.25);
}
.product-badge--jasa {
    color: #38BDF8;
    background: rgba(56,189,248,0.08);
    border-color: rgba(56,189,248,0.25);
}
.product-badge--empty {
    color: var(--color-muted);
    background: rgba(255,255,255,0.04);
    border-color: rgba(255,255,255,0.08);
}

.product-card__title {
    font-size: 1.05rem;
    font-weight: 700;
    line-height: 1.4;
    margin: 0;
}
.product-card__title-link {
    color: var(--color-text);
    text-decoration: none;
    transition: color 0.25s ease;
}
.product-card__title-link:hover { color: #F5F5F5; }

.product-card__excerpt {
    font-size: 0.875rem;
    color: var(--color-muted);
    line-height: 1.65;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-card__footer {
    margin-top: auto;
    padding-top: 0.9rem;
    border-top: 1px solid var(--color-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.product-card__price {
    font-size: 1rem;
    font-weight: 800;
    color: #F5F5F5;
    letter-spacing: -0.01em;
}
.product-card__price--ask {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--rb-red);
    letter-spacing: 0;
}

.product-card__cta {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--color-muted);
    text-decoration: none;
    transition: color 0.25s ease, gap 0.25s ease;
}
.product-card__cta:hover { color: var(--rb-red); gap: 0.55rem; }

/* ══════════════════════════════════════════════════════
   PAGINATION
══════════════════════════════════════════════════════ */
.catalog-pagination { margin-top: 3rem; }

/* ══════════════════════════════════════════════════════
   EMPTY STATE
══════════════════════════════════════════════════════ */
.catalog-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 4.5rem 1.5rem;
    border: 1px dashed var(--color-border);
    border-radius: 1.25rem;
    background: rgba(255,255,255,0.015);
}

.catalog-empty__icon-wrap {
    width: 4.5rem;
    height: 4.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(220,38,38,0.06);
    border: 1px solid rgba(220,38,38,0.14);
    margin-bottom: 1.5rem;
}
.catalog-empty__icon { width: 2rem; height: 2rem; color: var(--rb-red); }

.catalog-empty__title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--color-text);
    margin: 0 0 0.6rem;
}

.catalog-empty__desc {
    font-size: 0.95rem;
    color: var(--color-muted);
    line-height: 1.7;
    max-width: 30rem;
    margin: 0 0 1.75rem;
}

.catalog-empty__cta {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.35rem;
    border: 1px solid var(--rb-red-border);
    border-radius: 999px;
    color: var(--rb-red);
    font-size: 0.85rem;
    font-weight: 700;
    text-decoration: none;
    transition: border-color 0.35s ease, color 0.3s ease, background 0.35s ease;
}
.catalog-empty__cta:hover {
    border-color: var(--rb-red);
    color: #F5F5F5;
    background: rgba(185,28,28,0.07);
}

@media (max-width: 640px) {
    .catalog-grid { grid-template-columns: 1fr; }
}
</style>
@endsection
