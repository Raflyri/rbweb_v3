@extends('layouts.public')

@section('meta_title', $item->title . ' — RBeverything Software Hub')
@section('meta_description', Str::limit(strip_tags($item->short_description), 160))

{{-- STEALTH: No-Index --}}
@section('meta_robots', 'noindex, nofollow, noarchive, nosnippet')

@section('content')
<style>
.sc-detail-container {
    max-width: 68rem;
    margin: 0 auto;
    padding: 2rem 1.5rem 6rem;
    position: relative;
    z-index: 2;
}

.sc-back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--color-muted);
    font-size: 0.875rem;
    text-decoration: none;
    margin-bottom: 2rem;
    transition: color 0.2s ease;
}

.sc-back-link:hover {
    color: #F1F5F9;
}

.sc-detail-header {
    margin-bottom: 2.5rem;
}

.sc-detail-pill-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.sc-detail-title {
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 900;
    letter-spacing: -0.03em;
    color: #FFFFFF;
    line-height: 1.2;
    margin: 0 0 1rem;
}

.sc-detail-lead {
    font-size: 1.15rem;
    color: #94A3B8;
    line-height: 1.7;
    margin: 0;
}

.sc-showcase-box {
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 1.25rem;
    overflow: hidden;
    margin-bottom: 3rem;
}

.sc-showcase-media {
    width: 100%;
    max-height: 480px;
    background: #070B14;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
}

.sc-showcase-img {
    width: 100%;
    height: auto;
    max-height: 480px;
    object-fit: cover;
}

.sc-launch-bar {
    padding: 1.5rem 2rem;
    background: rgba(0, 0, 0, 0.4);
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.sc-detail-content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2.5rem;
    margin-bottom: 4rem;
}

@media (max-width: 840px) {
    .sc-detail-content-grid {
        grid-template-columns: 1fr;
    }
}

.sc-content-section {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--color-border);
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
}

.sc-section-heading {
    font-size: 1.25rem;
    font-weight: 800;
    color: #FFFFFF;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sc-body-text {
    color: #CBD5E1;
    font-size: 0.95rem;
    line-height: 1.8;
    white-space: pre-line;
}

.sc-spec-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--color-border);
    border-radius: 1rem;
    padding: 1.75rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.sc-spec-row {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.sc-spec-label {
    font-size: 0.775rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--color-muted);
}

.sc-spec-val {
    font-size: 0.925rem;
    font-weight: 600;
    color: #F1F5F9;
}

/* Changelog card */
.sc-changelog-box {
    background: rgba(0, 0, 0, 0.35);
    border: 1px solid rgba(220, 38, 38, 0.2);
    border-radius: 0.75rem;
    padding: 1.25rem;
    font-size: 0.875rem;
    color: #E2E8F0;
    line-height: 1.7;
    white-space: pre-line;
    font-family: 'Inter', system-ui, sans-serif;
}
</style>

@php
    $catName = $item->softwareCategory?->name ?? $item->category ?? 'Umum';
@endphp

<div class="sc-detail-container">
    <a href="{{ route('software-catalog.index') }}" class="sc-back-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Kembali ke Katalog Software
    </a>

    <header class="sc-detail-header">
        <div class="sc-detail-pill-row">
            <span style="padding:0.25rem 0.75rem;border-radius:999px;font-size:0.775rem;font-weight:700;background:rgba(56,189,248,0.12);color:#38BDF8;border:1px solid rgba(56,189,248,0.25);">
                {{ $catName }}
            </span>
            <span style="padding:0.25rem 0.65rem;border-radius:999px;font-size:0.775rem;font-weight:700;background:rgba(220,38,38,0.18);color:#FCA5A5;border:1px solid rgba(220,38,38,0.35);">
                Versi {{ $item->version }}
            </span>
            @if($item->release_date)
                <span style="padding:0.25rem 0.65rem;border-radius:999px;font-size:0.775rem;font-weight:600;background:rgba(255,255,255,0.06);color:#CBD5E1;display:inline-flex;align-items:center;gap:0.35rem;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    Dirilis {{ $item->release_date->translatedFormat('d F Y') }}
                </span>
            @endif
            @if($item->badge_text)
                <span style="padding:0.25rem 0.75rem;border-radius:999px;font-size:0.775rem;font-weight:700;background:rgba(16,185,129,0.15);color:#34D399;border:1px solid rgba(16,185,129,0.35);">
                    {{ $item->badge_text }}
                </span>
            @endif
        </div>
        <h1 class="sc-detail-title">{{ $item->title }}</h1>
        <p class="sc-detail-lead">{{ $item->short_description }}</p>
    </header>

    {{-- Showcase Media Box & Launch Bar --}}
    <div class="sc-showcase-box">
        <div class="sc-showcase-media">
            @if($item->thumbnail)
                <img src="{{ asset('storage/' . $item->thumbnail) }}" alt="{{ $item->title }}" class="sc-showcase-img">
            @else
                <div style="padding:4rem 2rem;text-align:center;color:#64748B;">
                    <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 1rem;color:var(--rb-red);">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                    <div style="font-size:1.1rem;font-weight:700;color:#F1F5F9;">Preview Antarmuka Sistem</div>
                    <div style="font-size:0.85rem;margin-top:0.35rem;">Hubungi kami untuk demonstrasi video atau presentasi online.</div>
                </div>
            @endif
        </div>

        <div class="sc-launch-bar">
            <div>
                <div style="font-size:0.8rem;color:var(--color-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">Akses Uji Coba Langsung</div>
                <div style="font-size:0.95rem;color:#F1F5F9;font-weight:700;margin-top:0.2rem;">
                    @if($item->demo_credentials)
                        Akun Demo: <code style="color:#38BDF8;">{{ $item->demo_credentials }}</code>
                    @else
                        Siap diakses melalui web browser
                    @endif
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:1rem;">
                @if($item->demo_url)
                    <a href="{{ $item->demo_url }}" target="_blank" rel="noopener" class="rb-btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:0.5rem;padding:0.75rem 1.5rem;">
                        <span>Luncurkan Live Demo</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    </a>
                @endif
                <a href="https://wa.me/6281234567890?text={{ urlencode('Halo RBeverything, saya ingin konsultasi mengenai implementasi ' . $item->title) }}" target="_blank" rel="noopener" class="rb-btn-secondary" style="text-decoration:none;">
                    Tanya Produk
                </a>
            </div>
        </div>
    </div>

    {{-- Detail Grid: Specs & Description --}}
    <div class="sc-detail-content-grid">
        <div>
            @if($item->description)
                <div class="sc-content-section">
                    <h2 class="sc-section-heading">Tentang Solusi Ini</h2>
                    <div class="sc-body-text">{{ $item->description }}</div>
                </div>
            @endif

            @if(!empty($item->features))
                <div class="sc-content-section">
                    <h2 class="sc-section-heading">Fitur & Kapabilitas Unggulan</h2>
                    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:0.75rem;">
                        @foreach($item->features as $feat)
                            <li style="display:flex;align-items:flex-start;gap:0.75rem;font-size:0.95rem;color:#E2E8F0;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color:#34D399;margin-top:0.2rem;flex-shrink:0;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>{{ $feat }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Changelog / Catatan Rilis --}}
            @if($item->changelog)
                <div class="sc-content-section">
                    <h2 class="sc-section-heading">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--rb-red);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        Catatan Rilis (Changelog Versi {{ $item->version }})
                    </h2>
                    <div class="sc-changelog-box">{{ $item->changelog }}</div>
                </div>
            @endif
        </div>

        <div>
            <div class="sc-spec-card">
                <div class="sc-spec-row">
                    <span class="sc-spec-label">Kategori Solusi</span>
                    <span class="sc-spec-val">{{ $catName }}</span>
                </div>
                <div class="sc-spec-row">
                    <span class="sc-spec-label">Versi Produksi</span>
                    <span class="sc-spec-val" style="color:#FCA5A5;font-weight:700;">v{{ $item->version }}</span>
                </div>
                <div class="sc-spec-row">
                    <span class="sc-spec-label">Tanggal Rilis</span>
                    <span class="sc-spec-val">{{ $item->release_date ? $item->release_date->translatedFormat('d F Y') : 'Terbaru' }}</span>
                </div>
                <div class="sc-spec-row">
                    <span class="sc-spec-label">Arsitektur & Tech Stack</span>
                    <div style="display:flex;flex-wrap:wrap;gap:0.35rem;margin-top:0.35rem;">
                        @if(!empty($item->tech_stack))
                            @foreach($item->tech_stack as $tech)
                                <span style="font-size:0.75rem;padding:0.2rem 0.5rem;border-radius:0.3rem;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);color:#CBD5E1;font-family:'JetBrains Mono',monospace;">{{ $tech }}</span>
                            @endforeach
                        @else
                            <span class="sc-spec-val">-</span>
                        @endif
                    </div>
                </div>
                <div class="sc-spec-row">
                    <span class="sc-spec-label">Deployment Model</span>
                    <span class="sc-spec-val">Cloudflare Edge + Proxmox Private Cloud</span>
                </div>
                <div class="sc-spec-row">
                    <span class="sc-spec-label">Dukungan Lisensi</span>
                    <span class="sc-spec-val">On-Premise / SaaS / Managed Private Cloud</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
