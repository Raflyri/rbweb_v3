@extends('layouts.public')

@section('meta_title', 'RBeverything Arcade — Portal Game Web Interaktif & Futuristik')
@section('meta_description', 'Pusat game web interaktif RBeverything. Mainkan koleksi game browser seru tanpa instalasi, dioptimalkan untuk mobile dan desktop.')

@section('content')
<style>
/* ── ARCADE CYBER & GAMING DESIGN SYSTEM ───────────────────────── */
:root {
    --neon-crimson: #FF2E63;
    --neon-cyan: #08D9D6;
    --neon-violet: #8B5CF6;
    --neon-amber: #F59E0B;
    --cyber-dark: #070B14;
    --cyber-surface: rgba(15, 23, 42, 0.85);
    --cyber-border: rgba(255, 255, 255, 0.1);
}

.arcade-container {
    max-width: 80rem;
    margin: 0 auto;
    padding: 1rem 1.5rem 5rem;
    position: relative;
    z-index: 2;
}

/* Background Cyber Grid effect */
.arcade-grid-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 600px;
    background-image: 
        linear-gradient(to right, rgba(255, 46, 99, 0.05) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(8, 217, 214, 0.04) 1px, transparent 1px);
    background-size: 40px 40px;
    mask-image: radial-gradient(ellipse 60% 60% at 50% 20%, #000 20%, transparent 80%);
    -webkit-mask-image: radial-gradient(ellipse 60% 60% at 50% 20%, #000 20%, transparent 80%);
    pointer-events: none;
    z-index: 1;
}

/* ── Hero Header ──────────────────────────────────────────────── */
.arcade-hero {
    text-align: center;
    padding: 2rem 1rem 3rem;
    position: relative;
    z-index: 2;
}

.arcade-title {
    font-size: clamp(2.2rem, 5vw, 3.8rem);
    font-weight: 900;
    letter-spacing: -0.04em;
    line-height: 1.1;
    margin: 0 0 1rem;
    color: #FFFFFF;
}

.arcade-title-gradient {
    background: linear-gradient(135deg, #FF2E63 0%, #A855F7 45%, #08D9D6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 0 24px rgba(255, 46, 99, 0.35));
}

.arcade-subtitle {
    font-size: 1.05rem;
    color: #94A3B8;
    max-width: 42rem;
    margin: 0 auto 1.75rem;
    line-height: 1.7;
}

/* Feature stats pills */
.arcade-stats-row {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 1.25rem;
    margin-top: 0.5rem;
}

.arcade-stat-item {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.45rem 1rem;
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.035);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #CBD5E1;
    font-size: 0.825rem;
    font-weight: 600;
}

.arcade-stat-item svg {
    color: #08D9D6;
}

/* ── SPOTLIGHT FEATURED BANNER ────────────────────────────────── */
.arcade-spotlight {
    position: relative;
    border-radius: 1.5rem;
    background: linear-gradient(135deg, rgba(255, 46, 99, 0.1), rgba(15, 23, 42, 0.95) 45%, rgba(8, 217, 214, 0.08));
    border: 1px solid rgba(255, 46, 99, 0.35);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 30px rgba(255, 46, 99, 0.15);
    overflow: hidden;
    margin-bottom: 3.5rem;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.arcade-spotlight:hover {
    border-color: rgba(8, 217, 214, 0.6);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7), 0 0 40px rgba(8, 217, 214, 0.25);
}

.arcade-spotlight-inner {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 2.5rem;
    padding: 2.5rem;
    align-items: center;
}

@media (max-width: 900px) {
    .arcade-spotlight-inner {
        grid-template-columns: 1fr;
        padding: 1.5rem;
        gap: 1.75rem;
    }
}

.arcade-spotlight-preview {
    position: relative;
    border-radius: 1rem;
    overflow: hidden;
    aspect-ratio: 16/9;
    background: #030712;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5);
    display: block;
    text-decoration: none;
}

.arcade-spotlight-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.arcade-spotlight-preview:hover img {
    transform: scale(1.05);
}

.arcade-spotlight-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 40%, rgba(3, 7, 18, 0.85) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.25s ease;
}

.arcade-spotlight-preview:hover .arcade-spotlight-overlay {
    opacity: 1;
}

.arcade-play-big-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #FF2E63, #08D9D6);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FFFFFF;
    box-shadow: 0 0 25px rgba(255, 46, 99, 0.8);
    transform: scale(0.9);
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.arcade-spotlight-preview:hover .arcade-play-big-btn {
    transform: scale(1.1);
}

.arcade-spotlight-tag {
    position: absolute;
    top: 1rem;
    left: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.65rem;
    border-radius: 0.5rem;
    background: rgba(255, 46, 99, 0.9);
    backdrop-filter: blur(8px);
    color: #FFFFFF;
    font-size: 0.7rem;
    font-weight: 800;
    font-family: var(--font-mono), monospace;
    letter-spacing: 0.05em;
    box-shadow: 0 4px 12px rgba(255, 46, 99, 0.5);
}

.arcade-spotlight-content {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.arcade-spotlight-label {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: #08D9D6;
    font-family: var(--font-mono), monospace;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.arcade-spotlight-title {
    font-size: clamp(1.75rem, 3vw, 2.35rem);
    font-weight: 900;
    line-height: 1.2;
    color: #FFFFFF;
    margin: 0;
}

.arcade-spotlight-desc {
    color: #94A3B8;
    font-size: 0.95rem;
    line-height: 1.7;
    margin: 0;
}

.arcade-spotlight-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
}

.arcade-meta-pill {
    padding: 0.3rem 0.75rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    font-size: 0.75rem;
    color: #E2E8F0;
    font-weight: 600;
}

.arcade-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.85rem 1.85rem;
    border-radius: 0.85rem;
    background: linear-gradient(135deg, #FF2E63, #C53030);
    color: #FFFFFF;
    font-weight: 800;
    font-size: 0.95rem;
    letter-spacing: 0.02em;
    text-decoration: none;
    box-shadow: 0 0 25px rgba(255, 46, 99, 0.5);
    transition: all 0.25s ease;
    width: fit-content;
}

.arcade-cta-btn:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 0 35px rgba(255, 46, 99, 0.75), 0 0 15px rgba(8, 217, 214, 0.5);
    background: linear-gradient(135deg, #FF2E63, #8B5CF6);
    color: #FFFFFF;
}

/* ── SEARCH & CATEGORY TOOLBAR ────────────────────────────────── */
.arcade-toolbar-container {
    margin-bottom: 2.25rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.arcade-toolbar-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.25rem;
}

.arcade-section-heading {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.arcade-section-heading h2 {
    font-size: 1.45rem;
    font-weight: 800;
    color: #FFFFFF;
    margin: 0;
}

.arcade-badge-counter {
    padding: 0.2rem 0.65rem;
    border-radius: 9999px;
    background: rgba(8, 217, 214, 0.12);
    border: 1px solid rgba(8, 217, 214, 0.35);
    color: #08D9D6;
    font-size: 0.75rem;
    font-family: var(--font-mono), monospace;
    font-weight: 700;
}

.arcade-search-box {
    position: relative;
    min-width: 17rem;
}

@media (max-width: 600px) {
    .arcade-search-box {
        width: 100%;
    }
}

.arcade-search-input {
    width: 100%;
    padding: 0.6rem 1rem 0.6rem 2.5rem;
    border-radius: 0.75rem;
    background: rgba(15, 23, 42, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #F8FAFC;
    font-size: 0.85rem;
    transition: all 0.2s ease;
}

.arcade-search-input:focus {
    outline: none;
    border-color: #08D9D6;
    box-shadow: 0 0 16px rgba(8, 217, 214, 0.35);
    background: rgba(15, 23, 42, 0.98);
}

.arcade-search-icon {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    color: #64748B;
    pointer-events: none;
}

/* Category Filter Tabs */
.arcade-category-tabs {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.arcade-cat-tab {
    padding: 0.4rem 0.9rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #94A3B8;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.arcade-cat-tab:hover {
    color: #FFFFFF;
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.16);
}

.arcade-cat-tab.active {
    background: linear-gradient(135deg, rgba(255, 46, 99, 0.25), rgba(8, 217, 214, 0.25));
    border-color: #08D9D6;
    color: #FFFFFF;
    box-shadow: 0 0 14px rgba(8, 217, 214, 0.35);
}

/* ── GAME CARDS GRID ─────────────────────────────────────────── */
.arcade-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(19rem, 1fr));
    gap: 2rem;
}

.arcade-game-card {
    position: relative;
    border-radius: 1.25rem;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.arcade-game-card:hover {
    transform: translateY(-7px) scale(1.015);
    border-color: rgba(255, 46, 99, 0.5);
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.6), 0 0 25px rgba(255, 46, 99, 0.25);
    background: rgba(15, 23, 42, 0.9);
}

.arcade-card-media {
    position: relative;
    aspect-ratio: 16/9;
    overflow: hidden;
    background: #030712;
    display: block;
    text-decoration: none;
}

.arcade-card-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.arcade-game-card:hover .arcade-card-media img {
    transform: scale(1.08);
}

.arcade-card-media-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(255, 46, 99, 0.1), rgba(8, 217, 214, 0.05));
    color: #FF2E63;
}

.arcade-card-cat-badge {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    padding: 0.2rem 0.55rem;
    border-radius: 0.5rem;
    background: rgba(10, 15, 29, 0.85);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(8, 217, 214, 0.4);
    color: #08D9D6;
    font-size: 0.7rem;
    font-family: var(--font-mono), monospace;
    font-weight: 700;
    letter-spacing: 0.02em;
    z-index: 2;
}

.arcade-card-play-overlay {
    position: absolute;
    inset: 0;
    background: rgba(7, 11, 20, 0.65);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.25s ease;
    z-index: 3;
}

.arcade-game-card:hover .arcade-card-play-overlay {
    opacity: 1;
}

.arcade-pulse-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #FF2E63;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 20px rgba(255, 46, 99, 0.8);
    transform: scale(0.85);
    transition: transform 0.2s ease;
}

.arcade-game-card:hover .arcade-pulse-icon {
    transform: scale(1.05);
}

.arcade-card-body {
    padding: 1.4rem;
    display: flex;
    flex-direction: column;
    flex: 1;
    gap: 0.85rem;
}

.arcade-card-title {
    font-size: 1.2rem;
    font-weight: 800;
    color: #F8FAFC;
    margin: 0;
    line-height: 1.3;
    transition: color 0.2s ease;
}

.arcade-game-card:hover .arcade-card-title {
    color: #08D9D6;
}

.arcade-card-desc {
    font-size: 0.875rem;
    color: #94A3B8;
    line-height: 1.6;
    margin: 0;
    flex: 1;
}

.arcade-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 1rem;
    margin-top: auto;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.arcade-card-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1.15rem;
    border-radius: 0.6rem;
    background: rgba(255, 46, 99, 0.14);
    border: 1px solid rgba(255, 46, 99, 0.45);
    color: #FF2E63;
    font-size: 0.825rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
}

.arcade-game-card:hover .arcade-card-btn {
    background: #FF2E63;
    border-color: #FF2E63;
    color: #FFFFFF;
    box-shadow: 0 0 16px rgba(255, 46, 99, 0.6);
}

/* Empty State */
.arcade-empty-screen {
    text-align: center;
    padding: 5rem 2rem;
    border-radius: 1.5rem;
    background: rgba(15, 23, 42, 0.6);
    border: 1px dashed rgba(255, 255, 255, 0.12);
}
</style>

<div class="arcade-grid-bg" aria-hidden="true"></div>

<div class="arcade-container">
    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- HERO ARENA HEADER --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <header class="arcade-hero">
        <h1 class="arcade-title">
            NEXT-GEN <span class="arcade-title-gradient">ARCADE</span> PORTAL
        </h1>

        <p class="arcade-subtitle">
            Pusat permainan web interaktif yang dirancang khusus untuk dimainkan seketika langsung dari browser.
            Tanpa unduhan, performa instan, dan siap dimainkan di desktop maupun smartphone.
        </p>

        <div class="arcade-stats-row">
            <div class="arcade-stat-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                </svg>
                <span>Instan Tanpa Install</span>
            </div>
            <div class="arcade-stat-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                    <path d="M6 12h4m-2-2v4"/>
                    <line x1="15" y1="11" x2="15.01" y2="11"/>
                    <line x1="18" y1="13" x2="18.01" y2="13"/>
                </svg>
                <span>Keyboard & Touch Controls</span>
            </div>
        </div>
    </header>

    @if($games->count() > 0)
        @php
            // Only show spotlight banner if an admin specifically designated a game as spotlight
            $spotlightGame = $games->firstWhere('is_spotlight', true);
        @endphp

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- SPOTLIGHT FEATURED GAME SHOWCASE (Manual Triggered) --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        @if($spotlightGame)
            <section class="arcade-spotlight" aria-label="Game Unggulan">
                <div class="arcade-spotlight-inner">
                    <a href="{{ route('arcade.play', $spotlightGame->slug) }}" class="arcade-spotlight-preview" aria-label="Mainkan {{ $spotlightGame->title }}">
                        <span class="arcade-spotlight-tag">★ SPOTLIGHT GAME</span>
                        @if($spotlightGame->thumbnail)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($spotlightGame->thumbnail) }}"
                                 alt="{{ $spotlightGame->title }}"
                                 loading="eager">
                        @else
                            <div class="arcade-card-media-placeholder">
                                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                                    <path d="M6 12h4m-2-2v4"/>
                                    <line x1="15" y1="11" x2="15.01" y2="11"/>
                                    <line x1="18" y1="13" x2="18.01" y2="13"/>
                                </svg>
                            </div>
                        @endif
                        <div class="arcade-spotlight-overlay">
                            <div class="arcade-play-big-btn">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor">
                                    <polygon points="6 3 20 12 6 21 6 3"/>
                                </svg>
                            </div>
                        </div>
                    </a>

                    <div class="arcade-spotlight-content">
                        <div class="arcade-spotlight-label">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            Koleksi Unggulan Pilihan
                        </div>

                        <h2 class="arcade-spotlight-title">
                            {{ $spotlightGame->title }}
                        </h2>

                        @if($spotlightGame->description)
                            <p class="arcade-spotlight-desc">
                                {{ $spotlightGame->description }}
                            </p>
                        @endif

                        <div class="arcade-spotlight-meta">
                            @if($spotlightGame->category)
                                <span class="arcade-meta-pill" style="color:#08D9D6;border-color:rgba(8,217,214,0.35);">
                                    📁 {{ $spotlightGame->category->name }}
                                </span>
                            @endif
                            <span class="arcade-meta-pill">🕹️ Web Play</span>
                            <span class="arcade-meta-pill">⚡ HTML5</span>
                            <span class="arcade-meta-pill">📱 Mobile Responsive</span>
                        </div>

                        <div>
                            <a href="{{ route('arcade.play', $spotlightGame->slug) }}" class="arcade-cta-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <polygon points="6 3 20 12 6 21 6 3"/>
                                </svg>
                                Mainkan Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- GAME CATALOG WITH DYNAMIC CATEGORIES & LIVE SEARCH --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <section aria-label="Daftar Semua Game">
            <div class="arcade-toolbar-container">
                <div class="arcade-toolbar-top">
                    <div class="arcade-section-heading">
                        <h2>Katalog Permainan</h2>
                        <span id="game-counter" class="arcade-badge-counter">{{ $games->count() }} GAME TERSEDIA</span>
                    </div>

                    <div class="arcade-search-box">
                        <svg class="arcade-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text"
                               id="arcade-search-input"
                               class="arcade-search-input"
                               placeholder="Cari nama atau deskripsi game..."
                               autocomplete="off">
                    </div>
                </div>

                {{-- Dynamic Category Filter Tabs --}}
                @if(isset($categories) && $categories->isNotEmpty())
                    <div class="arcade-category-tabs" role="tablist" aria-label="Filter Kategori Game">
                        <button type="button" class="arcade-cat-tab active" data-category="all">
                            Semua ({{ $games->count() }})
                        </button>
                        @foreach($categories as $category)
                            @if($category->games_count > 0)
                                <button type="button" class="arcade-cat-tab" data-category="{{ $category->slug }}">
                                    {{ $category->name }} ({{ $category->games_count }})
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <div id="arcade-grid" class="arcade-cards-grid">
                @foreach($games as $game)
                    <article class="arcade-game-card"
                             data-title="{{ strtolower($game->title) }}"
                             data-desc="{{ strtolower($game->description ?? '') }}"
                             data-category="{{ $game->category?->slug ?? 'all' }}">
                        <a href="{{ route('arcade.play', $game->slug) }}" class="arcade-card-media" aria-label="Mainkan {{ $game->title }}">
                            @if($game->category)
                                <span class="arcade-card-cat-badge">{{ $game->category->name }}</span>
                            @endif

                            @if($game->thumbnail)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($game->thumbnail) }}"
                                     alt="{{ $game->title }}"
                                     loading="lazy">
                            @else
                                <div class="arcade-card-media-placeholder">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                                        <path d="M6 12h4m-2-2v4"/>
                                        <line x1="15" y1="11" x2="15.01" y2="11"/>
                                        <line x1="18" y1="13" x2="18.01" y2="13"/>
                                    </svg>
                                </div>
                            @endif

                            <div class="arcade-card-play-overlay">
                                <div class="arcade-pulse-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <polygon points="6 3 20 12 6 21 6 3"/>
                                    </svg>
                                </div>
                            </div>
                        </a>

                        <div class="arcade-card-body">
                            <h3 class="arcade-card-title">
                                <a href="{{ route('arcade.play', $game->slug) }}" style="color:inherit;text-decoration:none;">
                                    {{ $game->title }}
                                </a>
                            </h3>

                            @if($game->description)
                                <p class="arcade-card-desc">
                                    {{ \Illuminate\Support\Str::limit($game->description, 110) }}
                                </p>
                            @endif

                            <div class="arcade-card-footer">
                                <span style="font-size:0.75rem;color:#64748B;font-family:var(--font-mono), monospace;display:inline-flex;align-items:center;gap:0.35rem;">
                                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#10B981;"></span>
                                    Ready to Play
                                </span>

                                <a href="{{ route('arcade.play', $game->slug) }}" class="arcade-card-btn">
                                    <span>Mainkan</span>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14M12 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div id="arcade-no-match" style="display:none;text-align:center;padding:4rem 1.5rem;color:#94A3B8;">
                <p style="font-size:1.1rem;margin:0 0 0.5rem;color:#F1F5F9;font-weight:700;">Tidak ada game yang sesuai filter</p>
                <p style="font-size:0.9rem;margin:0;">Coba pilih kategori lain atau ganti kata kunci pencarian.</p>
            </div>
        </section>
    @else
        <div class="arcade-empty-screen">
            <div style="font-size:3rem;margin-bottom:1rem;">🕹️</div>
            <h3 style="font-size:1.4rem;font-weight:800;color:#F1F5F9;margin:0 0 0.5rem;">Arena Segera Dibuka</h3>
            <p style="font-size:0.95rem;color:#94A3B8;max-width:30rem;margin:0 auto 1.5rem;">
                Koleksi game interaktif kami sedang dalam tahap kurasi dan segera ditayangkan. Silakan kembali lagi nanti!
            </p>
            <a href="{{ $mainDomainUrl }}/" class="arcade-cta-btn" style="margin:0 auto;">
                Kembali ke Beranda
            </a>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('arcade-search-input');
    const catTabs = document.querySelectorAll('.arcade-cat-tab');
    const cards = document.querySelectorAll('.arcade-game-card');
    const noMatch = document.getElementById('arcade-no-match');
    const counter = document.getElementById('game-counter');

    let currentCategory = 'all';
    let searchQuery = '';

    function filterGames() {
        let visibleCount = 0;

        cards.forEach(card => {
            const title = card.getAttribute('data-title') || '';
            const desc = card.getAttribute('data-desc') || '';
            const cardCat = card.getAttribute('data-category') || '';

            const matchesCategory = (currentCategory === 'all' || cardCat === currentCategory);
            const matchesSearch = (!searchQuery || title.includes(searchQuery) || desc.includes(searchQuery));

            if (matchesCategory && matchesSearch) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noMatch) {
            noMatch.style.display = visibleCount === 0 ? 'block' : 'none';
        }
        if (counter) {
            counter.textContent = visibleCount + ' GAME TERSEDIA';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            searchQuery = e.target.value.toLowerCase().trim();
            filterGames();
        });
    }

    catTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            catTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.getAttribute('data-category') || 'all';
            filterGames();
        });
    });
});
</script>
@endsection
