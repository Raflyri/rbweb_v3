<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $game->title }} — RBeverything Arcade</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($game->description), 160) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --neon-red: #FF2E63;
            --neon-cyan: #08D9D6;
            --bg-game: #050811;
            --hud-height: 52px;
        }

        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: var(--bg-game);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #F1F5F9;
        }

        /* ── Full viewport column layout ── */
        .arcade-viewport {
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background-color: #000000;
        }

        /* ── Header HUD: Always anchored at top, NEVER covering game content ── */
        .arcade-hud-bar {
            position: relative;
            width: 100%;
            height: var(--hud-height);
            flex-shrink: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0 1.25rem;
            background: #090D18;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.6);
        }

        .arcade-hud-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 0;
        }

        .arcade-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #F8FAFC;
            text-decoration: none;
            font-size: 0.825rem;
            font-weight: 700;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .arcade-back-link:hover {
            background: rgba(255, 46, 99, 0.25);
            border-color: #FF2E63;
            color: #FF2E63;
            box-shadow: 0 0 14px rgba(255, 46, 99, 0.45);
        }

        .arcade-hud-game-info {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            border-left: 1px solid rgba(255, 255, 255, 0.12);
            padding-left: 1rem;
            min-width: 0;
        }

        .arcade-hud-title {
            font-size: 0.9rem;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: -0.01em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .arcade-live-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.15rem 0.55rem;
            border-radius: 9999px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #34D399;
            font-size: 0.65rem;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            flex-shrink: 0;
        }

        .arcade-live-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10B981;
            box-shadow: 0 0 6px #10B981;
            animation: liveDotPulse 1.5s infinite;
        }

        @keyframes liveDotPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.3); }
        }

        .arcade-hud-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .arcade-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #CBD5E1;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .arcade-action-btn:hover {
            background: rgba(8, 217, 214, 0.18);
            border-color: #08D9D6;
            color: #08D9D6;
            box-shadow: 0 0 12px rgba(8, 217, 214, 0.4);
            transform: scale(1.08);
        }

        /* ── Game Iframe Container: exactly remaining height below header ── */
        .arcade-frame-wrapper {
            position: relative;
            width: 100%;
            flex: 1 1 0%;
            min-height: 0;
            height: calc(100vh - var(--hud-height));
            overflow: hidden;
            background: #000000;
        }

        .arcade-iframe {
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
        }

        .arcade-loader {
            position: absolute;
            inset: 0;
            background: #050811;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            z-index: 10;
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }

        .arcade-loader.loaded {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .arcade-spinner {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-top-color: #FF2E63;
            border-right-color: #08D9D6;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Toast notification */
        .arcade-toast {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: rgba(8, 217, 214, 0.95);
            color: #030712;
            padding: 0.6rem 1.25rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 700;
            box-shadow: 0 8px 24px rgba(8, 217, 214, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.25s ease;
            z-index: 200;
        }

        .arcade-toast.show {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        @media (max-width: 640px) {
            :root {
                --hud-height: 48px;
            }
            .arcade-hud-bar {
                padding: 0 0.85rem;
            }
            .arcade-live-pill {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="arcade-viewport" id="arcade-viewport">
        {{-- Header HUD: firmly anchored at top, normal flow so it NEVER covers game --}}
        <header class="arcade-hud-bar" role="banner">
            <div class="arcade-hud-left">
                <a href="{{ route('arcade.index') }}" class="arcade-back-link" aria-label="Kembali ke Portal Arcade">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    <span>Arcade</span>
                </a>

                <div class="arcade-hud-game-info">
                    <span class="arcade-hud-title">{{ $game->title }}</span>
                    @if($game->category)
                        <span style="font-size:0.75rem;padding:0.12rem 0.5rem;border-radius:999px;background:rgba(8,217,214,0.15);color:#08D9D6;font-weight:700;border:1px solid rgba(8,217,214,0.3);flex-shrink:0;">
                            {{ $game->category->name }}
                        </span>
                    @endif
                    <span class="arcade-live-pill">
                        <span class="arcade-live-dot"></span>
                        Now Playing
                    </span>
                </div>
            </div>

            <div class="arcade-hud-right">
                <button type="button" class="arcade-action-btn" id="btn-reload" title="Muat Ulang Game" aria-label="Muat Ulang Game">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                        <path d="M3 3v5h5"/>
                        <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                        <path d="M16 21h5v-5"/>
                    </svg>
                </button>

                <button type="button" class="arcade-action-btn" id="btn-share" title="Salin Tautan Game" aria-label="Salin Tautan Game">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                        <polyline points="16 6 12 2 8 6"/>
                        <line x1="12" y1="2" x2="12" y2="15"/>
                    </svg>
                </button>

                <button type="button" class="arcade-action-btn" id="btn-fullscreen" title="Layar Penuh" aria-label="Layar Penuh">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
                    </svg>
                </button>
            </div>
        </header>

        {{-- Game Container: fills 100% of remaining space below header --}}
        <main class="arcade-frame-wrapper" role="main">
            <div class="arcade-loader" id="arcade-loader">
                <div class="arcade-spinner"></div>
                <p style="font-family:'JetBrains Mono', monospace; font-size:0.85rem; color:#94A3B8; letter-spacing:0.05em;">
                    MEMUAT PERMAINAN...
                </p>
            </div>

            <iframe
                id="game-iframe"
                src="{{ $game->game_url }}"
                class="arcade-iframe"
                title="{{ $game->title }}"
                allow="fullscreen; autoplay; gamepad; clipboard-read; clipboard-write; keyboard-map"
                allowfullscreen>
            </iframe>
        </main>

        <div id="arcade-toast" class="arcade-toast">Tautan berhasil disalin ke clipboard!</div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const iframe = document.getElementById('game-iframe');
        const loader = document.getElementById('arcade-loader');
        const btnReload = document.getElementById('btn-reload');
        const btnShare = document.getElementById('btn-share');
        const btnFullscreen = document.getElementById('btn-fullscreen');
        const toast = document.getElementById('arcade-toast');

        // Hide loader when iframe finishes loading
        if (iframe) {
            iframe.addEventListener('load', function () {
                if (loader) loader.classList.add('loaded');
            });
            setTimeout(function () {
                if (loader) loader.classList.add('loaded');
            }, 3000);
        }

        // Reload game button
        if (btnReload && iframe) {
            btnReload.addEventListener('click', function () {
                if (loader) loader.classList.remove('loaded');
                iframe.src = iframe.src;
            });
        }

        // Fullscreen toggle
        if (btnFullscreen) {
            btnFullscreen.addEventListener('click', function () {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(err => {
                        console.error('Fullscreen error:', err);
                    });
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                }
            });
        }

        // Share link
        if (btnShare && toast) {
            btnShare.addEventListener('click', function () {
                const url = window.location.href;
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(() => {
                        showToast();
                    });
                } else {
                    showToast();
                }
            });
        }

        function showToast() {
            toast.classList.add('show');
            setTimeout(function () {
                toast.classList.remove('show');
            }, 2500);
        }
    });
    </script>
</body>
</html>
