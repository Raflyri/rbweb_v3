<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $game->title }} — RBeverything Arcade</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($game->description), 160) }}">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: #090D16;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #F1F5F9;
        }
        .arcade-viewport {
            position: relative;
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .arcade-topbar {
            position: fixed;
            top: 0.75rem;
            left: 0.75rem;
            z-index: 50;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            background: rgba(9, 13, 22, 0.82);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45);
            transition: opacity 0.25s ease;
        }
        .arcade-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #F8FAFC;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .arcade-back-btn:hover {
            color: #EF4444;
        }
        .arcade-game-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #94A3B8;
            border-left: 1px solid rgba(255, 255, 255, 0.15);
            padding-left: 0.75rem;
        }
        .arcade-iframe {
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="arcade-viewport">
        <div class="arcade-topbar">
            <a href="{{ route('arcade.index') }}" class="arcade-back-btn" aria-label="Kembali ke Portal Arcade">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                Arcade
            </a>
            <span class="arcade-game-title">{{ $game->title }}</span>
        </div>

        <iframe
            src="{{ $game->game_url }}"
            class="arcade-iframe"
            title="{{ $game->title }}"
            allow="fullscreen; autoplay; gamepad; clipboard-read; clipboard-write"
            allowfullscreen>
        </iframe>
    </div>
</body>
</html>
