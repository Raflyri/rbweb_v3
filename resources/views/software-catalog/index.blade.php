@extends('layouts.public')

@section('meta_title', 'Software Catalog — RBeverything Integrated Solutions')
@section('meta_description', 'Katalog perangkat lunak dan sistem digital siap pakai dari RBeverything: POS, ERP, Sistem Ujian CBT, dan automasi proses bisnis.')

{{-- ══════════════════════════════════════════════════════
     STEALTH MODE: Melarang bot mesin pencari mengindeks
══════════════════════════════════════════════════════ --}}
@section('meta_robots', 'noindex, nofollow, noarchive, nosnippet')

@section('content')
<style>
/* ── SOFTWARE CATALOG THEME (Adhering to rb-theme.css) ── */
.sc-container {
    max-width: 80rem;
    margin: 0 auto;
    padding: 1.5rem 1.5rem 6rem;
    position: relative;
    z-index: 2;
}

/* Background Network Matrix Accent */
.sc-grid-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 520px;
    background-image: 
        linear-gradient(to right, rgba(220, 38, 38, 0.04) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
    background-size: 48px 48px;
    mask-image: radial-gradient(ellipse 65% 55% at 50% 15%, #000 25%, transparent 80%);
    -webkit-mask-image: radial-gradient(ellipse 65% 55% at 50% 15%, #000 25%, transparent 80%);
    pointer-events: none;
    z-index: 1;
}

/* ── Hero Section ── */
.sc-hero {
    text-align: center;
    padding: 3rem 1rem 2.5rem;
    position: relative;
    z-index: 2;
}

.sc-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.9rem;
    border-radius: 999px;
    background: rgba(220, 38, 38, 0.08);
    border: 1px solid rgba(220, 38, 38, 0.3);
    color: #F87171;
    font-size: 0.775rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin-bottom: 1.25rem;
}

.sc-badge-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #DC2626;
    box-shadow: 0 0 10px #DC2626;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); }
    70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(220, 38, 38, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
}

.sc-title {
    font-size: clamp(2.2rem, 5vw, 3.6rem);
    font-weight: 900;
    letter-spacing: -0.035em;
    line-height: 1.15;
    margin: 0 0 1.25rem;
    color: #FFFFFF;
}

.sc-title-gradient {
    background: linear-gradient(135deg, #FFFFFF 30%, #FCA5A5 70%, #DC2626 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 0 30px rgba(220, 38, 38, 0.25));
}

.sc-subtitle {
    font-size: 1.1rem;
    color: #94A3B8;
    max-width: 44rem;
    margin: 0 auto 2rem;
    line-height: 1.7;
}

/* ── Filter Tabs ── */
.sc-filters {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.6rem;
    margin-bottom: 3.5rem;
}

.sc-filter-btn {
    padding: 0.5rem 1.15rem;
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #94A3B8;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.sc-filter-btn:hover {
    background: rgba(255, 255, 255, 0.06);
    color: #F1F5F9;
    border-color: rgba(255, 255, 255, 0.15);
}

.sc-filter-btn.active {
    background: rgba(220, 38, 38, 0.15);
    border-color: rgba(220, 38, 38, 0.5);
    color: #FCA5A5;
    box-shadow: 0 0 16px rgba(220, 38, 38, 0.25);
}

/* ── Software Grid ── */
.sc-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 2rem;
}

@media (max-width: 640px) {
    .sc-grid {
        grid-template-columns: 1fr;
    }
}

/* ── Software Card ── */
.sc-card {
    background: rgba(15, 23, 42, 0.45);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 1.25rem;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all 0.28s ease;
    position: relative;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.sc-card:hover {
    transform: translateY(-4px);
    border-color: rgba(220, 38, 38, 0.4);
    box-shadow: 0 16px 36px -10px rgba(0, 0, 0, 0.7), 0 0 24px rgba(220, 38, 38, 0.15);
}

.sc-card.is-featured {
    border-color: rgba(220, 38, 38, 0.35);
    background: linear-gradient(180deg, rgba(30, 20, 25, 0.55) 0%, rgba(10, 10, 15, 0.75) 100%);
}

.sc-card-media {
    position: relative;
    width: 100%;
    height: 210px;
    background: #090D18;
    overflow: hidden;
}

.sc-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.sc-card:hover .sc-card-img {
    transform: scale(1.04);
}

.sc-card-media-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: radial-gradient(circle at 50% 40%, rgba(220, 38, 38, 0.15) 0%, #060912 80%);
    color: #64748B;
    gap: 0.75rem;
}

.sc-card-badge-row {
    position: absolute;
    top: 1rem;
    left: 1rem;
    right: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    pointer-events: none;
    z-index: 3;
}

.sc-card-pill {
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    backdrop-filter: blur(8px);
}

.sc-pill-category {
    background: rgba(15, 23, 42, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #38BDF8;
}

.sc-pill-highlight {
    background: rgba(220, 38, 38, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #FFFFFF;
    box-shadow: 0 0 12px rgba(220, 38, 38, 0.5);
}

.sc-card-body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

/* Version & Release Date Metadata Row */
.sc-meta-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.65rem;
    flex-wrap: wrap;
}

.sc-version-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.75rem;
    font-weight: 800;
    color: #F87171;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.sc-date-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.75rem;
    color: #94A3B8;
    background: rgba(255, 255, 255, 0.035);
    border: 1px solid rgba(255, 255, 255, 0.06);
    padding: 0.15rem 0.5rem;
    border-radius: 0.35rem;
    font-family: 'JetBrains Mono', monospace;
}

.sc-card-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #FFFFFF;
    margin: 0 0 0.5rem;
    line-height: 1.3;
}

.sc-card-desc {
    color: #94A3B8;
    font-size: 0.9rem;
    line-height: 1.6;
    margin: 0 0 1.25rem;
    flex-grow: 1;
}

/* Feature bullets */
.sc-features-list {
    list-style: none;
    padding: 0;
    margin: 0 0 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.sc-features-item {
    font-size: 0.825rem;
    color: #CBD5E1;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sc-features-item svg {
    color: #34D399;
    flex-shrink: 0;
}

/* Tech stack pills */
.sc-tech-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-bottom: 1.5rem;
}

.sc-tech-badge {
    padding: 0.2rem 0.55rem;
    border-radius: 0.35rem;
    font-size: 0.72rem;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #CBD5E1;
    font-family: 'JetBrains Mono', monospace;
}

/* Card Action Footer */
.sc-card-footer {
    padding-top: 1.25rem;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.sc-btn-demo {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.7rem 1.25rem;
    border-radius: 0.65rem;
    background: #DC2626;
    color: #FFFFFF;
    font-size: 0.875rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 0 16px rgba(220, 38, 38, 0.35);
}

.sc-btn-demo:hover {
    background: #B91C1C;
    box-shadow: 0 0 24px rgba(220, 38, 38, 0.6);
    transform: translateY(-1px);
}

.sc-btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.7rem 1rem;
    border-radius: 0.65rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #CBD5E1;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.sc-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #FFFFFF;
}

/* Demo Credentials Hint Box */
.sc-cred-box {
    margin-top: 0.75rem;
    padding: 0.55rem 0.75rem;
    border-radius: 0.5rem;
    background: rgba(0, 0, 0, 0.4);
    border: 1px dashed rgba(255, 255, 255, 0.12);
    font-size: 0.775rem;
    color: #94A3B8;
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.sc-cred-box code {
    color: #38BDF8;
    font-family: 'JetBrains Mono', monospace;
}

/* ── Bottom CTA ── */
.sc-bottom-cta {
    margin-top: 5rem;
    padding: 3rem 2rem;
    border-radius: 1.5rem;
    background: radial-gradient(circle at 50% 0%, rgba(220, 38, 38, 0.12) 0%, rgba(10, 10, 15, 0.9) 80%);
    border: 1px solid rgba(220, 38, 38, 0.25);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.sc-cta-title {
    font-size: 1.85rem;
    font-weight: 800;
    color: #FFFFFF;
    margin: 0 0 0.75rem;
}

.sc-cta-desc {
    color: #94A3B8;
    max-width: 36rem;
    margin: 0 auto 1.75rem;
    font-size: 0.95rem;
    line-height: 1.6;
}
</style>

<div class="sc-grid-overlay"></div>

<div class="sc-container">
    {{-- ── Hero Section ── --}}
    <section class="sc-hero">
        <!--div class="sc-badge">
            <span class="sc-badge-dot"></span>
            RBeverything Software Hub
        </div>-->
        <h1 class="sc-title">
            Solusi Perangkat Lunak <br class="hidden sm:inline">
            <span class="sc-title-gradient">Terintegrasi & Siap Pakai</span>
        </h1>
        <p class="sc-subtitle">
            Eksplorasi portofolio perangkat lunak siap pakai dan teruji: Point of Sale, ERP multi-satuan, hingga platform ujian digital CBT. Uji coba langsung via live demo di bawah ini.
        </p>

        {{-- ── Dynamic Category Filters ── --}}
        @if($categories->count() > 0)
            <div class="sc-filters">
                <button class="sc-filter-btn active" data-filter="all">Semua Software ({{ $items->count() }})</button>
                @foreach($categories as $cat)
                    <button class="sc-filter-btn" data-filter="{{ $cat->slug }}">
                        {{ $cat->name }} ({{ $cat->published_software_count }})
                    </button>
                @endforeach
            </div>
        @endif
    </section>

    {{-- ── Software Cards Grid ── --}}
    <div class="sc-grid" id="softwareGrid">
        @forelse($items as $item)
            @php
                $catSlug = $item->softwareCategory?->slug ?? Str::slug($item->category ?? 'umum');
                $catName = $item->softwareCategory?->name ?? $item->category ?? 'Umum';
            @endphp
            <article class="sc-card {{ $item->is_featured ? 'is-featured' : '' }}" data-category="{{ $catSlug }}">
                {{-- Media Preview --}}
                <div class="sc-card-media">
                    <div class="sc-card-badge-row">
                        <span class="sc-card-pill sc-pill-category">{{ $catName }}</span>
                        @if($item->badge_text)
                            <span class="sc-card-pill sc-pill-highlight">{{ $item->badge_text }}</span>
                        @endif
                    </div>

                    @if($item->thumbnail)
                        <img src="{{ asset('storage/' . $item->thumbnail) }}" alt="{{ $item->title }}" class="sc-card-img" loading="lazy">
                    @else
                        <div class="sc-card-media-placeholder">
                            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                <line x1="8" y1="21" x2="16" y2="21"></line>
                                <line x1="12" y1="17" x2="12" y2="21"></line>
                            </svg>
                            <span style="font-size:0.8rem;font-weight:600;letter-spacing:0.04em;">RBEVERYTHING SUITE v{{ $item->version }}</span>
                        </div>
                    @endif
                </div>

                {{-- Card Content --}}
                <div class="sc-card-body">
                    {{-- Version & Release Date Metadata --}}
                    <div class="sc-meta-row">
                        <span class="sc-version-pill">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            Versi {{ $item->version }}
                        </span>

                        @if($item->release_date)
                            <span class="sc-date-pill" title="Tanggal Rilis Resmi">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                {{ $item->release_date->translatedFormat('d M Y') }}
                            </span>
                        @endif
                    </div>

                    <h2 class="sc-card-title">{{ $item->title }}</h2>
                    <p class="sc-card-desc">{{ $item->short_description }}</p>

                    {{-- Feature Bullets --}}
                    @if(!empty($item->features))
                        <ul class="sc-features-list">
                            @foreach(array_slice($item->features, 0, 3) as $feat)
                                <li class="sc-features-item">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>{{ $feat }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    {{-- Tech Stack Pills --}}
                    @if(!empty($item->tech_stack))
                        <div class="sc-tech-row">
                            @foreach($item->tech_stack as $tech)
                                <span class="sc-tech-badge">{{ $tech }}</span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Demo Credentials Hint if available --}}
                    @if($item->demo_credentials)
                        <div class="sc-cred-box">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                            <span>Demo: <code>{{ $item->demo_credentials }}</code></span>
                        </div>
                    @endif

                    {{-- Actions Bar --}}
                    <div class="sc-card-footer">
                        @if($item->demo_url)
                            <a href="{{ $item->demo_url }}" target="_blank" rel="noopener" class="sc-btn-demo">
                                <span>Coba Live Demo</span>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                            </a>
                        @else
                            <button disabled class="sc-btn-demo" style="opacity:0.5;cursor:not-allowed;box-shadow:none;">
                                Demo Segera Hadir
                            </button>
                        @endif

                        <a href="{{ route('software-catalog.show', $item->slug) }}" class="sc-btn-secondary" title="Lihat detail lengkap & changelog">
                            Detail
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div style="grid-column:1/-1;text-align:center;padding:4rem 1rem;background:rgba(255,255,255,0.02);border:1px dashed var(--color-border);border-radius:1rem;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--color-muted);margin:0 auto 1rem;">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
                <h3 style="font-size:1.1rem;color:#F1F5F9;margin:0 0 0.5rem;">Katalog Software Sedang Diperbarui</h3>
                <p style="color:var(--color-muted);font-size:0.9rem;max-width:28rem;margin:0 auto;">
                    Belum ada software yang ditayangkan ke publik saat ini. Silakan kunjungi kembali beberapa saat lagi.
                </p>
            </div>
        @endforelse
    </div>

    {{-- ── Bottom Inquiry CTA ── --}}
    <section class="sc-bottom-cta">
        <h2 class="sc-cta-title">Butuh Solusi Software Custom untuk Bisnis Anda?</h2>
        <p class="sc-cta-desc">
            Seluruh sistem di katalog ini dapat dikustomisasi sesuai alur kerja (*workflow*) institusi, sekolah, maupun operasional toko/perusahaan Anda. Hubungi tim engineering kami untuk konsultasi arsitektur.
        </p>
        <div style="display:flex;align-items:center;justify-content:center;gap:1rem;flex-wrap:wrap;">
            <a href="https://wa.me/6281234567890?text={{ urlencode('Halo RBeverything, saya tertarik untuk konsultasi dan kustomisasi solusi software katalog.') }}" target="_blank" rel="noopener" class="rb-btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                Konsultasi via WhatsApp
            </a>
            <a href="https://rbeverything.com/tentang-kami" class="rb-btn-secondary" style="text-decoration:none;">
                Tentang Tim RBeverything
            </a>
        </div>
    </section>
</div>

<script>
// Dynamic Category Filter Interaction
document.addEventListener('DOMContentLoaded', () => {
    const filterBtns = document.querySelectorAll('.sc-filter-btn');
    const cards = document.querySelectorAll('#softwareGrid .sc-card');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filterValue = btn.getAttribute('data-filter');

            cards.forEach(card => {
                const cardCat = card.getAttribute('data-category');
                if (filterValue === 'all' || cardCat === filterValue) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>
@endsection
