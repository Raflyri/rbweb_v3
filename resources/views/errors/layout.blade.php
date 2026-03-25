{{--
    ════════════════════════════════════════════════════════════════
    RBeverything — Error Page Shared Layout
    ════════════════════════════════════════════════════════════════
    Self-contained: No @vite, no external JS, no CDN dependencies.
    All CSS is inline so this works even when asset pipeline is broken.

    Slots:
      $errorCode   — numeric HTTP status code (e.g. "404")
      $errorIcon   — SVG path(s) for the hero icon
      $title       — Short user-facing title
      $description — Longer user-facing message (production)
      $debugNote   — Optional extra note shown only in debug mode
      $hideBack    — (bool) hide the "Back" button (default false)
      $hideContact — (bool) hide the contact support link
    ════════════════════════════════════════════════════════════════
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $errorCode ?? 'Error' }} — RBeverything</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">

    <style>
        /* ── Reset & Base ─────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --rb-red:         #DC2626;
            --rb-red-dim:     #B91C1C;
            --rb-red-glow:    rgba(185,28,28,0.35);
            --rb-red-border:  rgba(220,38,38,0.28);
            --rb-red-subtle:  rgba(220,38,38,0.06);
            --color-text:     #C9D1D9;
            --color-muted:    #586069;
            --color-border:   rgba(255,255,255,0.07);
            --font-sans:      'Inter', system-ui, sans-serif;
            --font-mono:      'JetBrains Mono', 'Fira Code', monospace;
        }

        html, body {
            height: 100%;
            background-color: #000;
            color: var(--color-text);
            font-family: var(--font-sans);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* ── Background Layers ────────────────────────────────────── */
        .rb-err-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .rb-err-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(
                ellipse 120% 80% at 50% 0%,
                #0d0d0d 0%, #060606 40%, #000 75%
            );
        }

        /* Subtle red radial glow at top */
        .rb-err-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(
                ellipse 70% 50% at 50% -10%,
                rgba(185,28,28,0.08) 0%, transparent 60%
            );
        }

        /* Node-network SVG overlay */
        .rb-err-overlay {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            opacity: 0.04;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='220' height='220'%3E%3Ccircle cx='20' cy='20' r='2' fill='%23fff'/%3E%3Ccircle cx='110' cy='10' r='1.5' fill='%23fff'/%3E%3Ccircle cx='200' cy='30' r='2' fill='%23fff'/%3E%3Ccircle cx='50' cy='110' r='1.5' fill='%23fff'/%3E%3Ccircle cx='150' cy='90' r='2' fill='%23fff'/%3E%3Ccircle cx='210' cy='110' r='1.5' fill='%23fff'/%3E%3Ccircle cx='10' cy='200' r='1.5' fill='%23fff'/%3E%3Ccircle cx='110' cy='210' r='2' fill='%23fff'/%3E%3Ccircle cx='200' cy='195' r='1.5' fill='%23fff'/%3E%3Ccircle cx='75' cy='55' r='1' fill='%23fff'/%3E%3Ccircle cx='165' cy='155' r='1' fill='%23fff'/%3E%3Cline x1='20' y1='20' x2='110' y2='10' stroke='%23fff' stroke-width='0.5'/%3E%3Cline x1='110' y1='10' x2='200' y2='30' stroke='%23fff' stroke-width='0.5'/%3E%3Cline x1='20' y1='20' x2='50' y2='110' stroke='%23fff' stroke-width='0.5'/%3E%3Cline x1='110' y1='10' x2='150' y2='90' stroke='%23fff' stroke-width='0.5'/%3E%3Cline x1='200' y1='30' x2='210' y2='110' stroke='%23fff' stroke-width='0.5'/%3E%3Cline x1='50' y1='110' x2='150' y2='90' stroke='%23fff' stroke-width='0.5'/%3E%3Cline x1='150' y1='90' x2='210' y2='110' stroke='%23fff' stroke-width='0.5'/%3E%3Cline x1='50' y1='110' x2='110' y2='210' stroke='%23fff' stroke-width='0.5'/%3E%3Cline x1='150' y1='90' x2='200' y2='195' stroke='%23fff' stroke-width='0.5'/%3E%3Cline x1='10' y1='200' x2='110' y2='210' stroke='%23fff' stroke-width='0.5'/%3E%3Cline x1='110' y1='210' x2='200' y2='195' stroke='%23fff' stroke-width='0.5'/%3E%3C/svg%3E");
            background-size: 220px 220px;
        }

        /* ── Page Wrapper ─────────────────────────────────────────── */
        .rb-err-page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Header / Logo ────────────────────────────────────────── */
        .rb-err-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }

        .rb-err-logo {
            font-size: 1.2rem;
            font-weight: 900;
            letter-spacing: -0.04em;
            background: linear-gradient(135deg, #E53E3E, #C53030);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }

        /* ── Main Content ─────────────────────────────────────────── */
        .rb-err-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 1.5rem;
            text-align: center;
        }

        /* ── Error Code ───────────────────────────────────────────── */
        .rb-err-code {
            font-family: var(--font-mono);
            font-size: clamp(6rem, 18vw, 11rem);
            font-weight: 700;
            letter-spacing: -0.06em;
            line-height: 1;
            background: linear-gradient(135deg, #DC2626 0%, #991B1B 60%, #7F1D1D 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: none;
            user-select: none;
            position: relative;
            margin-bottom: 0.25rem;
            animation: rb-err-fade-in 0.6s ease both;
        }

        /* Subtle glow behind error code */
        .rb-err-code::after {
            content: attr(data-code);
            position: absolute;
            inset: 0;
            font-family: inherit;
            font-size: inherit;
            font-weight: inherit;
            letter-spacing: inherit;
            background: inherit;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: blur(28px);
            opacity: 0.35;
            z-index: -1;
        }

        /* ── Icon ─────────────────────────────────────────────────── */
        .rb-err-icon-wrap {
            width: 4rem;
            height: 4rem;
            border-radius: 1rem;
            background: var(--rb-red-subtle);
            border: 1px solid var(--rb-red-border);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1.5rem auto 0;
            animation: rb-err-fade-in 0.7s 0.1s ease both;
        }

        /* ── Title ────────────────────────────────────────────────── */
        .rb-err-title {
            font-size: clamp(1.5rem, 4vw, 2.25rem);
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #F1F5F9;
            line-height: 1.2;
            margin-top: 1rem;
            max-width: 32rem;
            animation: rb-err-fade-in 0.7s 0.15s ease both;
        }

        /* ── Description ──────────────────────────────────────────── */
        .rb-err-desc {
            font-size: 1rem;
            color: var(--color-muted);
            line-height: 1.7;
            max-width: 36rem;
            margin-top: 0.875rem;
            font-weight: 400;
            animation: rb-err-fade-in 0.7s 0.2s ease both;
        }

        /* ── Sandbox Badge ────────────────────────────────────────── */
        .rb-err-sandbox-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.9rem;
            border-radius: 9999px;
            background: rgba(217,119,6,0.1);
            border: 1px solid rgba(217,119,6,0.3);
            color: #F59E0B;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-family: var(--font-mono);
            margin-top: 1.5rem;
            animation: rb-err-fade-in 0.6s 0.25s ease both;
        }

        .rb-err-sandbox-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #F59E0B;
            animation: rb-err-ping 1.5s cubic-bezier(0,0,0.2,1) infinite;
        }

        /* ── Debug Panel ──────────────────────────────────────────── */
        .rb-err-debug {
            margin-top: 1.5rem;
            width: 100%;
            max-width: 42rem;
            text-align: left;
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 0.875rem;
            overflow: hidden;
            animation: rb-err-fade-in 0.7s 0.3s ease both;
        }

        .rb-err-debug-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.025);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.35);
            font-family: var(--font-mono);
        }

        .rb-err-debug-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
        }

        .rb-err-debug-body {
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .rb-err-debug-row {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.8rem;
            font-family: var(--font-mono);
            word-break: break-all;
        }

        .rb-err-debug-label {
            color: rgba(255,255,255,0.3);
            min-width: 5.5rem;
            flex-shrink: 0;
            font-size: 0.72rem;
            padding-top: 0.05rem;
        }

        .rb-err-debug-value {
            color: #C9D1D9;
        }

        .rb-err-debug-value.method-get  { color: #34D399; }
        .rb-err-debug-value.method-post { color: #38BDF8; }
        .rb-err-debug-value.method-delete { color: #F87171; }
        .rb-err-debug-value.method-put  { color: #A78BFA; }

        /* Exception panel */
        .rb-err-exception {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: rgba(220,38,38,0.04);
            border-top: 1px solid rgba(220,38,38,0.1);
        }

        .rb-err-exception-class {
            font-size: 0.78rem;
            font-family: var(--font-mono);
            color: #F87171;
            font-weight: 600;
        }

        .rb-err-exception-msg {
            font-size: 0.8rem;
            font-family: var(--font-mono);
            color: #C9D1D9;
            margin-top: 0.25rem;
            line-height: 1.5;
        }

        /* ── Actions ──────────────────────────────────────────────── */
        .rb-err-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
            margin-top: 2.5rem;
            animation: rb-err-fade-in 0.7s 0.35s ease both;
        }

        .rb-err-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.7rem 1.6rem;
            border-radius: 9999px;
            background: transparent;
            border: 1px solid var(--rb-red-border);
            color: var(--rb-red);
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: border-color 0.35s ease, box-shadow 0.35s ease, color 0.3s ease, transform 0.3s ease;
            cursor: pointer;
            font-family: var(--font-sans);
        }

        .rb-err-btn-primary:hover {
            border-color: var(--rb-red);
            color: #F5F5F5;
            box-shadow: 0 0 14px var(--rb-red-glow), inset 0 0 12px rgba(185,28,28,0.06);
            transform: translateY(-1px);
            background: var(--rb-red-subtle);
        }

        .rb-err-btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.7rem 1.5rem;
            border-radius: 9999px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            color: var(--color-muted);
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: border-color 0.35s ease, background 0.35s ease, color 0.3s ease, transform 0.3s ease;
            cursor: pointer;
            font-family: var(--font-sans);
        }

        .rb-err-btn-ghost:hover {
            border-color: rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.06);
            color: #E2E8F0;
            transform: translateY(-1px);
        }

        /* ── Footer ───────────────────────────────────────────────── */
        .rb-err-footer {
            padding: 1.25rem 2rem;
            border-top: 1px solid rgba(220,38,38,0.06);
            text-align: center;
        }

        .rb-err-footer-text {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.2);
        }

        .rb-err-footer-text a {
            color: rgba(220,38,38,0.6);
            text-decoration: none;
            transition: color 0.3s;
        }

        .rb-err-footer-text a:hover {
            color: var(--rb-red);
        }

        /* ── Decorative divider line ──────────────────────────────── */
        .rb-err-divider {
            width: 3rem;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(220,38,38,0.4), transparent);
            margin: 1.5rem auto 0;
        }

        /* ── Animations ───────────────────────────────────────────── */
        @keyframes rb-err-fade-in {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes rb-err-ping {
            0%   { transform: scale(1); opacity: 1; }
            75%, 100% { transform: scale(2); opacity: 0; }
        }

        /* ── Responsive ───────────────────────────────────────────── */
        @media (max-width: 600px) {
            .rb-err-header { padding: 1.25rem 1.25rem; }
            .rb-err-main   { padding: 3rem 1.25rem; }
            .rb-err-debug  { margin-left: -0.25rem; margin-right: -0.25rem; }
            .rb-err-debug-label { min-width: 4rem; }
        }
    </style>
</head>
<body>

    {{-- ── Background Layers ─────────────────────────────────────── --}}
    <div class="rb-err-bg" aria-hidden="true"></div>
    <div class="rb-err-overlay" aria-hidden="true"></div>

    <div class="rb-err-page">

        {{-- ── Header ─────────────────────────────────────────────── --}}
        <header class="rb-err-header">
            <a href="/" class="rb-err-logo" aria-label="RBeverything — Home">RBeverything</a>
        </header>

        {{-- ── Main Content ────────────────────────────────────────── --}}
        <main class="rb-err-main" role="main">

            {{-- Big Error Code --}}
            <div class="rb-err-code" data-code="{{ $errorCode ?? 'ERR' }}" aria-label="HTTP Error {{ $errorCode ?? '' }}">
                {{ $errorCode ?? 'ERR' }}
            </div>

            {{-- Icon --}}
            <div class="rb-err-icon-wrap" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                     stroke="#DC2626" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    {!! $errorIcon ?? '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>' !!}
                </svg>
            </div>

            {{-- Title --}}
            <h1 class="rb-err-title">{{ $title ?? 'Something went wrong' }}</h1>

            {{-- Description --}}
            <p class="rb-err-desc">{{ $description ?? 'An unexpected error has occurred. Please try again or contact support if the problem persists.' }}</p>

            <div class="rb-err-divider" aria-hidden="true"></div>

            {{-- ── Sandbox / Debug Info ────────────────────────────── --}}
            @if(config('app.debug'))
                <div class="rb-err-sandbox-badge" role="status" aria-label="Sandbox environment">
                    <span class="rb-err-sandbox-dot" aria-hidden="true"></span>
                    SANDBOX — {{ strtoupper(config('app.env')) }}
                </div>

                @if(isset($exception) || isset($debugNote))
                <div class="rb-err-debug" role="complementary" aria-label="Debug information">
                    <div class="rb-err-debug-header">
                        <span class="rb-err-debug-dot" style="background:#FF5F57"></span>
                        <span class="rb-err-debug-dot" style="background:#FEBC2E"></span>
                        <span class="rb-err-debug-dot" style="background:#28C840"></span>
                        Debug Panel — visible in non-production only
                    </div>

                    <div class="rb-err-debug-body">
                        <div class="rb-err-debug-row">
                            <span class="rb-err-debug-label">URL</span>
                            <span class="rb-err-debug-value">{{ request()->fullUrl() }}</span>
                        </div>
                        <div class="rb-err-debug-row">
                            <span class="rb-err-debug-label">Method</span>
                            <span class="rb-err-debug-value method-{{ strtolower(request()->method()) }}">
                                {{ request()->method() }}
                            </span>
                        </div>
                        <div class="rb-err-debug-row">
                            <span class="rb-err-debug-label">HTTP Status</span>
                            <span class="rb-err-debug-value">{{ $errorCode ?? 'N/A' }}</span>
                        </div>
                        @if(isset($debugNote))
                        <div class="rb-err-debug-row">
                            <span class="rb-err-debug-label">Note</span>
                            <span class="rb-err-debug-value">{{ $debugNote }}</span>
                        </div>
                        @endif
                    </div>

                    @if(isset($exception))
                    <div class="rb-err-exception">
                        <div class="rb-err-exception-class">{{ get_class($exception) }}</div>
                        <div class="rb-err-exception-msg">{{ $exception->getMessage() ?: '(no message)' }}</div>
                        @if($exception->getFile())
                        <div class="rb-err-debug-row" style="margin-top:0.5rem;">
                            <span class="rb-err-debug-label">File</span>
                            <span class="rb-err-debug-value" style="font-size:0.72rem;color:#586069;">
                                {{ str_replace(base_path().'/', '', $exception->getFile()) }}:{{ $exception->getLine() }}
                            </span>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
            @endif
            {{-- ── /Debug Info ─────────────────────────────────────── --}}

            {{-- ── Action Buttons ──────────────────────────────────── --}}
            <div class="rb-err-actions">
                @if(empty($hideBack))
                <button class="rb-err-btn-ghost" onclick="history.length > 1 ? history.back() : window.location.href='/'" aria-label="Go back">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Go Back
                </button>
                @endif

                <a href="/" class="rb-err-btn-primary" aria-label="Return to homepage">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Go Home
                </a>

                @if(empty($hideContact) && !config('app.debug'))
                <a href="mailto:{{ config('mail.from.address', 'hello@rbeverything.com') }}"
                   class="rb-err-btn-ghost" aria-label="Contact support">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    Contact Support
                </a>
                @endif
            </div>

        </main>

        {{-- ── Footer ──────────────────────────────────────────────── --}}
        <footer class="rb-err-footer">
            <p class="rb-err-footer-text">
                © {{ date('Y') }} <a href="/">RBeverything</a>.
                @if(config('app.debug'))
                    Debug mode active · {{ config('app.env') }}
                @else
                    All rights reserved.
                @endif
            </p>
        </footer>

    </div><!-- /.rb-err-page -->

</body>
</html>
