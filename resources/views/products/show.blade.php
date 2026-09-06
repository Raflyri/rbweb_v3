@extends('layouts.public')

@php
    use App\Support\ProductType;

    $name        = $product->translate('name', $locale);
    $summary     = $product->translate('short_description', $locale);
    $description = $product->translate('description', $locale);
    $price       = $product->formattedPrice();

    $seoTitle       = $product->translate('meta_title', $locale) ?: $name;
    $seoDescription = $product->translate('meta_description', $locale)
                   ?: ($summary ?: Str::limit(strip_tags($description), 160));

    $ogImage = $product->thumbnail
             ? \Illuminate\Support\Facades\Storage::url($product->thumbnail)
             : asset('images/og-default.png');

    $waMessage = rawurlencode(
        "Halo RBeverything, saya ingin bertanya tentang \"{$name}\" ("
        . route('products.show', $product->slug) . ')'
    );
@endphp

{{-- ════════════════════════════════════════════════════════════
     SEO — Product detail
════════════════════════════════════════════════════════════ --}}
@section('meta_title',       $seoTitle)
@section('meta_description', $seoDescription)
@section('canonical',        route('products.show', $product->slug))
@section('og_type',          'product')
@section('og_title',         $seoTitle . ' — RBeverything')
@section('og_description',   $seoDescription)
@section('og_image',         $ogImage)
{{-- A hidden product is only reachable by an admin previewing it; it must
     never be indexed while it is switched off. --}}
@if(! $product->is_active)
    @section('meta_robots', 'noindex, nofollow')
@endif

@section('head_extra')
    @php
        // Built here rather than inside @json(...): Blade parses a directive's
        // argument itself and trips over a multi-line nested array.
        $jsonLd = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $name,
            'description' => $seoDescription,
            'image'       => url($ogImage),
            'url'         => route('products.show', $product->slug),
            'brand'       => ['@type' => 'Brand', 'name' => 'RBeverything'],
        ];

        // An item quoted on request has no Offer to advertise; emitting a
        // priceless one would be an invalid rich result.
        if ($product->hasPrice()) {
            $jsonLd['offers'] = [
                '@type'         => 'Offer',
                'price'         => (string) $product->price,
                'priceCurrency' => $product->currency,
                'availability'  => $product->isInStock()
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url'           => route('products.show', $product->slug),
            ];
        }
    @endphp
    <script type="application/ld+json">
        {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')

    <section class="pdp" aria-label="{{ $name }}">
        <div class="rb-section" style="padding-bottom:4rem;">

            <nav class="catalog-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="catalog-breadcrumb__link">Home</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                <a href="{{ route('products.index') }}" class="catalog-breadcrumb__link">Produk &amp; Layanan</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                <span class="catalog-breadcrumb__current">{{ Str::limit($name, 40) }}</span>
            </nav>

            @unless($product->is_active)
                <div class="pdp-preview-note" role="status">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    Pratinjau admin — produk ini belum tayang untuk publik.
                </div>
            @endunless

            <div class="pdp-layout">

                {{-- ── Media ──────────────────────────────────── --}}
                <div class="pdp-media">
                    @if($product->thumbnail)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($product->thumbnail) }}"
                             alt="{{ $name }}" class="pdp-media__img">
                    @else
                        <div class="pdp-media__placeholder">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none"
                                 stroke="rgba(220,38,38,0.22)" stroke-width="1"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                                <path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- ── Summary / purchase panel ───────────────── --}}
                <div class="pdp-panel">

                    <div class="product-card__badges">
                        <span class="product-badge product-badge--{{ $product->type }}">
                            {{ ProductType::label($product->type) }}
                        </span>
                        @if($product->is_featured)
                            <span class="product-badge product-badge--featured">Unggulan</span>
                        @endif
                        @if(! $product->isInStock())
                            <span class="product-badge product-badge--empty">Stok habis</span>
                        @endif
                    </div>

                    <h1 class="pdp-title">{{ $name }}</h1>

                    @if($summary !== '')
                        <p class="pdp-summary">{{ $summary }}</p>
                    @endif

                    <div class="pdp-price-box">
                        @if($price)
                            <span class="pdp-price">{{ $price }}</span>
                            @if($product->tracksStock())
                                <span class="pdp-stock">
                                    {{ $product->isInStock() ? 'Stok tersedia: ' . $product->stock : 'Sedang kosong' }}
                                </span>
                            @endif
                        @else
                            <span class="pdp-price pdp-price--ask">Hubungi Kami</span>
                            <span class="pdp-stock">Harga menyesuaikan kebutuhan — mari bicara dulu.</span>
                        @endif
                    </div>

                    <div class="pdp-actions">
                        @if($contact['whatsapp'])
                            <a href="{{ $contact['whatsapp'] }}?text={{ $waMessage }}"
                               class="rb-btn-primary pdp-btn" target="_blank" rel="noopener">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                                </svg>
                                Tanya via WhatsApp
                            </a>
                        @endif

                        <a href="mailto:{{ $contact['email'] }}?subject={{ rawurlencode('Pertanyaan produk: ' . $name) }}"
                           class="rb-btn-ghost pdp-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>
                            </svg>
                            Kirim Email
                        </a>
                    </div>

                    @if($product->hasPrice())
                        {{-- Phase 3 replaces this note with the real "Pesan Sekarang"
                             button. Saying so plainly beats a dead button that
                             looks clickable. --}}
                        <p class="pdp-note">
                            Pemesanan online sedang disiapkan. Sementara ini, pesanan diproses lewat
                            WhatsApp atau email — kami balas secepatnya.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if(trim(strip_tags($description)) !== '')
        <div class="rb-divider"></div>

        <section class="pdp-description" aria-label="Deskripsi lengkap">
            <div class="rb-section" style="padding-top:3.5rem;padding-bottom:4rem;">
                <span class="rb-section-label">Detail</span>
                <div class="pdp-prose">
                    {{-- Safe to render raw: ProductObserver runs Purifier over the
                         description on every save (profile 'product'), so what is
                         stored is already sanitised. --}}
                    {!! $description !!}
                </div>
            </div>
        </section>
    @endif

    @if($related->isNotEmpty())
        <div class="rb-divider"></div>

        <section class="pdp-related" aria-label="Lainnya">
            <div class="rb-section" style="padding-top:3.5rem;padding-bottom:5rem;">
                <span class="rb-section-label">Lainnya</span>
                <h2 class="pdp-related__title">{{ ProductType::label($product->type) }} lainnya</h2>

                <div class="catalog-grid">
                    @foreach($related as $item)
                        @php
                            $itemName  = $item->translate('name', $locale);
                            $itemPrice = $item->formattedPrice();
                            $itemUrl   = route('products.show', $item->slug);
                        @endphp
                        <article class="product-card">
                            <a href="{{ $itemUrl }}" class="product-card__cover-link" aria-label="{{ $itemName }}" tabindex="-1">
                                @if($item->thumbnail)
                                    <div class="product-card__cover">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($item->thumbnail) }}"
                                             alt="{{ $itemName }}" class="product-card__img" loading="lazy">
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
                            </a>
                            <div class="product-card__body">
                                <h3 class="product-card__title">
                                    <a href="{{ $itemUrl }}" class="product-card__title-link">{{ $itemName }}</a>
                                </h3>
                                <div class="product-card__footer">
                                    <span class="product-card__price {{ $itemPrice ? '' : 'product-card__price--ask' }}">
                                        {{ $itemPrice ?? 'Hubungi Kami' }}
                                    </span>
                                    <a href="{{ $itemUrl }}" class="product-card__cta">
                                        Lihat
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
            </div>
        </section>
    @endif

@endsection

@section('styles')
<style>
/* Breadcrumb, cards and badges are shared with the catalogue listing. */
.catalog-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: var(--color-muted);
    margin-bottom: 2rem;
    flex-wrap: wrap;
}
.catalog-breadcrumb__link {
    color: var(--color-muted);
    text-decoration: none;
    transition: color 0.25s ease;
}
.catalog-breadcrumb__link:hover { color: var(--color-text); }
.catalog-breadcrumb__current { color: var(--color-text); font-weight: 500; }

.pdp-preview-note {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    margin-bottom: 1.75rem;
    border: 1px solid rgba(251,191,36,0.28);
    background: rgba(251,191,36,0.07);
    color: #FBBF24;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
}

/* ── Layout ─────────────────────────────────────────── */
.pdp-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
    gap: 3rem;
    align-items: start;
}

.pdp-media {
    border: 1px solid var(--color-border);
    border-radius: 1.25rem;
    overflow: hidden;
    background: rgba(255,255,255,0.02);
}
.pdp-media__img {
    width: 100%;
    height: auto;
    display: block;
    aspect-ratio: 4 / 3;
    object-fit: cover;
}
.pdp-media__placeholder {
    aspect-ratio: 4 / 3;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pdp-panel { display: flex; flex-direction: column; gap: 1.1rem; }

.pdp-title {
    font-size: clamp(1.75rem, 3.5vw, 2.6rem);
    font-weight: 900;
    letter-spacing: -0.025em;
    line-height: 1.15;
    color: #F5F5F5;
    margin: 0;
}

.pdp-summary {
    font-size: 1rem;
    color: var(--color-muted);
    line-height: 1.75;
    margin: 0;
}

.pdp-price-box {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    padding: 1.25rem;
    border: 1px solid var(--color-border);
    border-radius: 1rem;
    background: rgba(255,255,255,0.025);
}
.pdp-price {
    font-size: 1.9rem;
    font-weight: 900;
    letter-spacing: -0.02em;
    color: #F5F5F5;
}
.pdp-price--ask { font-size: 1.4rem; color: var(--rb-red); }
.pdp-stock { font-size: 0.8rem; color: var(--color-muted); }

.pdp-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; }
.pdp-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.7rem 1.4rem;
    border-radius: 999px;
    font-size: 0.875rem;
    font-weight: 700;
    text-decoration: none;
}

.pdp-note {
    font-size: 0.8rem;
    color: var(--color-muted);
    line-height: 1.65;
    margin: 0;
}

.product-badge--featured {
    color: var(--rb-red);
    background: rgba(220,38,38,0.08);
    border-color: var(--rb-red-border);
}

/* ── Description prose ──────────────────────────────── */
.pdp-prose {
    margin-top: 1.25rem;
    max-width: 48rem;
    color: var(--color-text);
    font-size: 1rem;
    line-height: 1.85;
}
.pdp-prose h2, .pdp-prose h3 {
    color: #F5F5F5;
    font-weight: 800;
    letter-spacing: -0.015em;
    margin: 2rem 0 0.75rem;
}
.pdp-prose h2 { font-size: 1.5rem; }
.pdp-prose h3 { font-size: 1.2rem; }
.pdp-prose p  { margin: 0 0 1.1rem; }
.pdp-prose ul, .pdp-prose ol { margin: 0 0 1.1rem 1.25rem; }
.pdp-prose li { margin-bottom: 0.4rem; }
.pdp-prose a  { color: var(--rb-red); text-decoration: underline; }
.pdp-prose img { border-radius: 0.75rem; }
.pdp-prose blockquote {
    border-left: 2px solid var(--rb-red-border);
    padding-left: 1rem;
    color: var(--color-muted);
    margin: 0 0 1.1rem;
}
.pdp-prose table {
    width: 100%;
    border-collapse: collapse;
    margin: 0 0 1.25rem;
    font-size: 0.9rem;
}
.pdp-prose th, .pdp-prose td {
    border: 1px solid var(--color-border);
    padding: 0.6rem 0.75rem;
    text-align: left;
}

.pdp-related__title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--color-text);
    margin: 0 0 1.75rem;
}

/* ── Cards (shared with the listing) ────────────────── */
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
.product-card__cover { aspect-ratio: 4 / 3; overflow: hidden; background: rgba(255,255,255,0.02); }
.product-card__img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.6s ease; }
.product-card:hover .product-card__img { transform: scale(1.04); }
.product-card__cover--placeholder { display: flex; align-items: center; justify-content: center; }
.product-card__body { display: flex; flex-direction: column; gap: 0.75rem; padding: 1.25rem; flex: 1; }
.product-card__badges { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.product-card__title { font-size: 1.05rem; font-weight: 700; line-height: 1.4; margin: 0; }
.product-card__title-link { color: var(--color-text); text-decoration: none; transition: color 0.25s ease; }
.product-card__title-link:hover { color: #F5F5F5; }
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
.product-card__price { font-size: 1rem; font-weight: 800; color: #F5F5F5; letter-spacing: -0.01em; }
.product-card__price--ask { font-size: 0.85rem; font-weight: 700; color: var(--rb-red); letter-spacing: 0; }
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

@media (max-width: 900px) {
    .pdp-layout { grid-template-columns: 1fr; gap: 2rem; }
}
@media (max-width: 640px) {
    .catalog-grid { grid-template-columns: 1fr; }
}
</style>
@endsection
