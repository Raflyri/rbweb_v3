<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RBeverything — Everything You Need!</title>
    <meta name="description"
        content="RBeverything is your trusted technology partner for IT consulting, web development, AI implementation, system architecture, and tech training.">
    <meta property="og:title" content="RBeverything — Everything You Need!">
    <meta property="og:description" content="Your trusted technology partner. Smarter systems, bolder results.">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900&family=JetBrains+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/lenis@1.1.20/dist/lenis.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/scroll-effects.js'])

    {{-- ── i18n data injected for client-side switching ── --}}
    @php
        echo '<' . 'script' . ">\n";
        echo 'window.RB_I18N = ' . Illuminate\Support\Js::from($i18n)->toHtml() . ";\n";
        echo 'window.RB_PAGE_DATA = ' . Illuminate\Support\Js::from($pageData)->toHtml() . ";\n";
        echo '</' . 'script' . '>';
    @endphp
</head>

<body>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- BACKGROUND --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="rb-bg" aria-hidden="true">
        <div class="rb-bg-gradient"></div>
        <div class="rb-dot-grid"></div>
        <div class="rb-binary-overlay"></div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- HEADER --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <header id="rb-nav" class="rb-nav" role="banner">
        <div
            style="max-width:80rem;margin:0 auto;padding:0 1.75rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;">

            {{-- LOGO --}}
            <a href="/" style="text-decoration:none;flex-shrink:0;" aria-label="RBeverything — Home">
                <span
                    style="font-size:1.3rem;font-weight:900;letter-spacing:-0.04em;background:linear-gradient(135deg,#E53E3E,#C53030);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                    RBeverything
                </span>
            </a>

            {{-- DESKTOP NAV --}}
            <nav class="rb-desktop-nav" style="display:flex;align-items:center;gap:2rem;" aria-label="Main navigation">
                <a href="#products" class="rb-nav-link" data-i18n="nav.products">Products</a>
                <a href="#services" class="rb-nav-link" data-i18n="nav.services">Services</a>
                <a href="#blog" class="rb-nav-link" data-i18n="nav.blog">Blog</a>
                <a href="#about" class="rb-nav-link" data-i18n="nav.about">About Us</a>
            </nav>

            {{-- DESKTOP RIGHT CLUSTER: lang switcher + CTA --}}
            <div class="rb-desktop-nav" style="display:flex;align-items:center;gap:0.875rem;">
                {{-- Language Switcher --}}
                <div class="rb-lang-switcher" role="group" aria-label="Language selector">
                    <button class="rb-lang-btn" data-lang="en" aria-label="Switch to English">EN</button>
                    <button class="rb-lang-btn" data-lang="id" aria-label="Ganti ke Bahasa Indonesia">ID</button>
                    <button class="rb-lang-btn" data-lang="ms" aria-label="Tukar ke Bahasa Malaysia">MY</button>
                    <button class="rb-lang-btn" data-lang="ja" aria-label="日本語に切り替え">JA</button>
                </div>

                <a href="mailto:{{ $settings->contact_email ?: 'hello@rbeverything.com' }}" class="rb-btn-primary"
                    id="nav-cta" data-i18n="nav.cta">
                    Let's Collaborate
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            {{-- HAMBURGER --}}
            <button id="rb-hamburger" class="rb-hamburger" aria-label="Open navigation menu" aria-expanded="false"
                aria-controls="rb-mobile-menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    {{-- ── MOBILE MENU ── --}}
    <div id="rb-mobile-menu" class="rb-mobile-menu" role="dialog" aria-modal="true" aria-label="Mobile navigation">
        <nav style="display:flex;flex-direction:column;gap:0.25rem;">
            <a href="#products"
                style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);"
                data-i18n="nav.products">Products</a>
            <a href="#services"
                style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);"
                data-i18n="nav.services">Services</a>
            <a href="#blog"
                style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);"
                data-i18n="nav.blog">Blog</a>
            <a href="#about"
                style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);"
                data-i18n="nav.about">About Us</a>
        </nav>
        {{-- Mobile lang switcher --}}
        <div class="rb-lang-switcher" style="margin-top:1.5rem;width:fit-content;">
            <button class="rb-lang-btn" data-lang="en">EN</button>
            <button class="rb-lang-btn" data-lang="id">ID</button>
            <button class="rb-lang-btn" data-lang="ms">MY</button>
            <button class="rb-lang-btn" data-lang="ja">JA</button>
        </div>
        <a href="mailto:{{ $settings->contact_email ?: 'hello@rbeverything.com' }}" class="rb-btn-hero"
            style="margin-top:1.25rem;width:fit-content;" data-i18n="nav.cta">
            Let's Collaborate
        </a>
    </div>

    <main>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- HERO --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <section class="rb-hero" aria-label="Hero">
            <div id="rb-hero-content"
                style="position:relative;z-index:1;width:100%;max-width:56rem;margin:0 auto;text-align:center;">

                {{-- Status badge --}}
                <div class="rb-hero-badge" data-reveal>
                    <span class="ping-dot" aria-hidden="true"></span>
                    <span data-i18n="hero.badge">System Online · V3 Active</span>
                </div>

                {{-- Static headline --}}
                <h1 class="rb-hero-h1" data-reveal data-reveal-delay="1">
                    <span data-i18n="hero.headline">Everything you need for</span>
                </h1>

                {{-- Typewriter line --}}
                <div class="rb-hero-typewriter-line" data-reveal data-reveal-delay="2" aria-live="polite"
                    aria-atomic="true">
                    <span id="typewriter-target" class="rb-gradient-text"></span><span class="rb-cursor"
                        aria-hidden="true"></span>
                </div>

                {{-- Subtitle --}}
                <p class="rb-hero-subtitle" data-reveal data-reveal-delay="3" data-i18n="hero.subtitle">
                    Your trusted technology partner. We craft smarter systems, bolder digital products, and AI-powered
                    solutions — all under one roof.
                </p>

                {{-- CTAs --}}
                <div class="rb-hero-ctas" data-reveal data-reveal-delay="4">
                    <a href="mailto:{{ $settings->contact_email ?: 'hello@rbeverything.com' }}" class="rb-btn-hero"
                        data-i18n="nav.cta">
                        Let's Collaborate
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="#services" class="rb-btn-ghost" data-i18n="hero.cta_secondary">Explore Services</a>
                </div>
            </div>

            {{-- Scroll indicator --}}
            <a href="#products" class="rb-scroll-indicator" aria-label="Scroll to products">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14M5 12l7 7 7-7" />
                </svg>
            </a>
        </section>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- PRODUCTS --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <section id="products" aria-label="Products">
            <div class="rb-section">
                <div style="margin-bottom:4rem;" data-reveal>
                    <span class="rb-section-label" data-i18n="products.label">Products</span>
                    <h2 class="rb-section-title" data-i18n="products.title">Tools built for the modern web</h2>
                    <p class="rb-section-desc" data-i18n="products.desc">Powerful standalone micro-tools engineered for
                        developers and teams.</p>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem;">

                    {{-- ── CARD 1: Passive Liveness Detection ── --}}
                    <article class="rb-card" data-reveal data-reveal-delay="1" style="padding:2rem;">
                        <div class="rb-card-content">
                            <div
                                style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                                <span class="rb-tag tag-violet" data-i18n="products.liveness.tag">AI · Computer
                                    Vision</span>
                                <span
                                    style="font-size:0.68rem;font-family:'JetBrains Mono',monospace;color:#334155;">v1.1</span>
                            </div>
                            <h3 style="font-size:1.3rem;font-weight:800;letter-spacing:-0.02em;margin-bottom:0.5rem;color:#F1F5F9;"
                                data-i18n="products.liveness.name">Passive Liveness Detection</h3>
                            <p style="font-size:0.875rem;color:#64748B;line-height:1.7;margin-bottom:1.5rem;"
                                data-i18n="products.liveness.desc">
                                Frictionless identity verification. Anti-spoofing biometric detection that distinguishes
                                real faces from photos, videos, and 3D masks — without user interaction.
                            </p>

                            {{-- Animated liveness demo --}}
                            <div class="rb-liveness-demo">
                                {{-- Corner accents --}}
                                <div class="rb-corner-tr" aria-hidden="true"></div>
                                <div class="rb-corner-bl" aria-hidden="true"></div>
                                {{-- Scan line --}}
                                <div class="rb-scan-line" aria-hidden="true"></div>
                                {{-- Face oval with pulse rings --}}
                                <div class="rb-face-oval" aria-hidden="true">
                                    <div class="rb-face-ring"></div>
                                    <div class="rb-face-ring"></div>
                                    <div class="rb-face-ring"></div>
                                    {{-- Face silhouette SVG --}}
                                    <svg width="32" height="36" viewBox="0 0 40 46" fill="none"
                                        stroke="rgba(167,139,250,0.5)" stroke-width="1.5" stroke-linecap="round">
                                        <ellipse cx="20" cy="17" rx="11" ry="13" />
                                        <path d="M6 44c0-8 6-14 14-14s14 6 14 14" />
                                    </svg>
                                </div>
                                {{-- Status badge --}}
                                <div class="rb-liveness-status">
                                    <span class="rb-liveness-dot" aria-hidden="true"></span>
                                    LIVENESS: REAL
                                </div>
                            </div>

                            <a href="#contact"
                                style="display:inline-flex;align-items:center;gap:0.4rem;font-size:0.85rem;font-weight:600;color:#A78BFA;text-decoration:none;"
                                data-i18n="products.liveness.cta">
                                Learn More
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </article>

                    {{-- ── CARD 2: Base64 Suite (interactive) ── --}}
                    <article class="rb-card" data-reveal data-reveal-delay="2" style="padding:2rem;">
                        <div class="rb-card-content">
                            <div
                                style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                                <span class="rb-tag tag-sky" data-i18n="products.base64.tag">Encoder / Decoder</span>
                                <span
                                    style="font-size:0.68rem;font-family:'JetBrains Mono',monospace;color:#334155;">v2.4</span>
                            </div>
                            <h3 style="font-size:1.3rem;font-weight:800;letter-spacing:-0.02em;margin-bottom:0.5rem;color:#F1F5F9;"
                                data-i18n="products.base64.name">Base64 Suite</h3>
                            <p style="font-size:0.875rem;color:#64748B;line-height:1.7;margin-bottom:1.5rem;"
                                data-i18n="products.base64.desc">
                                Encode, decode, and validate Base64 strings in real-time with support for URL-safe
                                variants.
                            </p>

                            {{-- Live Base64 encoder --}}
                            <div style="display:flex;flex-direction:column;gap:0.6rem;margin-bottom:1.5rem;">
                                {{-- Input label --}}
                                <div
                                    style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.2rem;">
                                    <span
                                        style="font-size:0.65rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#334155;">Input</span>
                                </div>
                                <textarea id="rb-b64-input" class="rb-base64-input" rows="3"
                                    data-i18n="products.base64.placeholder" placeholder="Type something to encode..."
                                    aria-label="Text to encode in Base64"></textarea>

                                {{-- Arrow divider --}}
                                <div style="text-align:center;color:#334155;font-size:0.8rem;">↓ Base64</div>

                                {{-- Output --}}
                                <div
                                    style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.2rem;">
                                    <span
                                        style="font-size:0.65rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#334155;"
                                        data-i18n="products.base64.output_label">Base64 output</span>
                                </div>
                                <div class="rb-base64-output-wrap">
                                    <div id="rb-b64-output" class="rb-base64-output" aria-live="polite"
                                        aria-label="Base64 encoded output"></div>
                                    <button id="rb-b64-copy" class="rb-base64-copy-btn"
                                        aria-label="Copy Base64 output">Copy</button>
                                </div>
                            </div>

                            <a href="https://tools.rbeverything.com/base64" target="_blank" rel="noopener"
                                style="display:inline-flex;align-items:center;gap:0.4rem;font-size:0.85rem;font-weight:600;color:#38BDF8;text-decoration:none;"
                                data-i18n="products.base64.cta">
                                Open Tool
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" />
                                    <polyline points="15 3 21 3 21 9" />
                                    <line x1="10" y1="14" x2="21" y2="3" />
                                </svg>
                            </a>
                        </div>
                    </article>

                    {{-- ── CARD 3: Portfolio Platform ── --}}
                    <article class="rb-card" data-reveal data-reveal-delay="3" style="padding:2rem;">
                        <div class="rb-card-content">
                            <div
                                style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                                <span class="rb-tag tag-emerald" data-i18n="products.portfolio.tag">Platform</span>
                                <span
                                    style="font-size:0.68rem;font-family:'JetBrains Mono',monospace;color:#334155;">v3.0</span>
                            </div>
                            <h3 style="font-size:1.3rem;font-weight:800;letter-spacing:-0.02em;margin-bottom:0.5rem;color:#F1F5F9;"
                                data-i18n="products.portfolio.name">Portfolio Platform</h3>
                            <p style="font-size:0.875rem;color:#64748B;line-height:1.7;margin-bottom:1.5rem;"
                                data-i18n="products.portfolio.desc">
                                Every user gets a personalised /@slug page to showcase skills, experience, and
                                achievements.
                            </p>

                            {{-- Code window demo --}}
                            <div class="rb-demo-window" style="margin-bottom:1.5rem;">
                                <div class="rb-demo-titlebar">
                                    <div class="rb-demo-dot" style="background:#FF5F57"></div>
                                    <div class="rb-demo-dot" style="background:#FEBC2E"></div>
                                    <div class="rb-demo-dot" style="background:#28C840"></div>
                                    <span
                                        style="font-size:0.63rem;color:#334155;margin-left:0.75rem;font-family:'JetBrains Mono',monospace;">rbeverything.com/@rafly</span>
                                </div>
                                <div class="rb-demo-code"><span style="color:#38BDF8">const</span> <span
                                        style="color:#34D399">user</span> = {
                                    slug: <span style="color:#818CF8">'@rafly'</span>,
                                    role: <span style="color:#818CF8">'Premium'</span>,
                                    skills: [<span style="color:#FB7185">'Laravel'</span>, <span
                                        style="color:#FB7185">'AI'</span>]
                                    };</div>
                            </div>

                            <a href="/client-area/register"
                                style="display:inline-flex;align-items:center;gap:0.4rem;font-size:0.85rem;font-weight:600;color:#34D399;text-decoration:none;"
                                data-i18n="products.portfolio.cta">
                                Build Your Page
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </article>

                </div>
            </div>
        </section>

        <div class="rb-divider"></div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- SERVICES + WORKFLOW --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <section id="services" aria-label="Services and Workflow">
            <div class="rb-section">

                {{-- Section header --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:end;margin-bottom:3.5rem;"
                    data-reveal>
                    <div>
                        <span class="rb-section-label" data-i18n="services.label">What We Do</span>
                        <h2 class="rb-section-title" data-i18n="services.title">Services that move the needle</h2>
                    </div>
                    <div>
                        <p class="rb-section-desc" data-i18n="services.desc">
                            From concept to deployment, we bring strategy, engineering, and creativity together to
                            deliver results that matter.
                        </p>
                        <a href="mailto:{{ $settings->contact_email ?: 'hello@rbeverything.com' }}" class="rb-btn-hero"
                            style="margin-top:1.5rem;display:inline-flex;font-size:0.9rem;padding:0.7rem 1.75rem;">
                            <span data-i18n="services.cta">Let's Collaborate</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                style="margin-left:0.4rem;">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- 6-tile services grid --}}
                @php
                    $serviceIcons = [
                        'consulting' => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>',
                        'webdev' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
                        'ai' => '<path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>',
                        'devops' => '<circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14"/>',
                        'training' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
                        'product' => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
                    ];
                @endphp

                <div class="rb-services-grid" style="margin-bottom:5rem;">
                    @foreach($pageData['services'] as $i => $service)
                        <div class="rb-service-tile" data-reveal data-reveal-delay="{{ $i + 1 }}">
                            @php $iconStyle = "background:{$service['color']}10;border-color:{$service['color']}20;"; @endphp
                            <div class="rb-service-icon" {!! 'style="' . $iconStyle . '"' !!}>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="{{ $service['color'] }}"
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    {!! $serviceIcons[$service['key']] ?? '' !!}
                                </svg>
                            </div>
                            <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:0.35rem;color:#F1F5F9;"
                                data-i18n="services.items.{{ $service['key'] }}.title">
                                {{ $service['key'] }}
                            </h3>
                            <p style="font-size:0.8rem;color:#64748B;line-height:1.65;"
                                data-i18n="services.items.{{ $service['key'] }}.desc">
                                Description for {{ $service['key'] }}.
                            </p>
                        </div>
                    @endforeach
                </div>

                {{-- ── Workflow ── --}}
                <div data-reveal>
                    <span class="rb-section-label" data-i18n="workflow.label">How We Work</span>
                    <h2 class="rb-section-title" style="font-size:clamp(1.6rem,3.5vw,2.8rem);"
                        data-i18n="workflow.title">From idea to launch in four steps</h2>
                </div>

                @php
                    $workflowIcons = [
                        'discovery' => '<circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>',
                        'design' => '<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/>',
                        'development' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
                        'liftoff' => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 00-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 012-3.95A12.88 12.88 0 0122 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 01-4 2z"/>',
                    ];
                @endphp

                <div class="rb-workflow-grid">
                    @foreach($pageData['workflow_steps'] as $idx => $step)
                        <div class="rb-workflow-step" data-reveal data-reveal-delay="{{ $idx + 1 }}">
                            <span class="rb-workflow-num" data-i18n="workflow.steps.{{ $step }}.num">0{{ $idx + 1 }}</span>
                            <div class="rb-workflow-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8"
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    {!! $workflowIcons[$step] ?? '' !!}
                                </svg>
                            </div>
                            <span class="rb-workflow-name"
                                data-i18n="workflow.steps.{{ $step }}.name">{{ ucfirst($step) }}</span>
                            <p class="rb-workflow-desc" data-i18n="workflow.steps.{{ $step }}.desc">Step description.</p>
                        </div>
                    @endforeach
                </div>

                {{-- Tech marquee --}}
                <div style="margin-top:5rem;" data-reveal>
                    <p style="font-size:0.68rem;letter-spacing:0.14em;text-transform:uppercase;color:#334155;font-weight:600;text-align:center;margin-bottom:1.5rem;"
                        data-i18n="services.techstrip_label">Technologies we work with</p>
                    <div class="rb-marquee-outer">
                        <div class="rb-marquee-track">
                            @foreach(array_merge($pageData['tech_stack'], $pageData['tech_stack']) as $tech)
                                <span class="rb-marquee-item">{{ $tech }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <div class="rb-divider"></div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- BLOG / ARTICLES --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <section id="blog" aria-label="Blog">
            <div class="rb-section">

                <div style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;margin-bottom:3rem;"
                    data-reveal>
                    <div>
                        <span class="rb-section-label" data-i18n="blog.label">Blog</span>
                        <h2 class="rb-section-title" style="margin-bottom:0;" data-i18n="blog.title">Latest articles
                        </h2>
                    </div>
                    <div style="display:flex;gap:0.75rem;">
                        <button id="rb-carousel-prev" class="rb-carousel-arrow" aria-label="Previous articles">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 12H5M12 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button id="rb-carousel-next" class="rb-carousel-arrow" aria-label="Next articles">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="rb-carousel-outer" data-reveal data-reveal-delay="1">
                    <div id="rb-carousel-track" class="rb-carousel-track">
                        @foreach($pageData['articles'] as $article)
                            <a href="{{ $article['href'] }}" class="rb-article-card">
                                @php $catStyle = 'color:' . $article['category_color'] . ';'; @endphp
                                <span class="rb-article-category" {!! 'style="' . $catStyle . '"' !!}>{{ $article['category'] }}</span>
                                <h3 class="rb-article-title">{{ $article['title'] }}</h3>
                                <p style="font-size:0.85rem;color:#64748B;line-height:1.6;flex:1;">{{ $article['excerpt'] }}
                                </p>
                                <span class="rb-article-date">{{ $article['date'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div style="margin-top:2.5rem;text-align:center;" data-reveal data-reveal-delay="2">
                    <a href="#"
                        style="font-size:0.875rem;font-weight:600;color:#64748B;text-decoration:none;border-bottom:1px solid #1E293B;padding-bottom:2px;transition:color 0.3s,border-color 0.3s;"
                        data-i18n="blog.view_all">View all articles →</a>
                </div>

            </div>
        </section>

        <div class="rb-divider"></div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- ABOUT --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <section id="about" aria-label="About">
            <div class="rb-section" style="max-width:52rem;text-align:center;">
                <span class="rb-section-label" data-reveal data-i18n="about.label">About RBeverything</span>
                <h2 class="rb-section-title" data-reveal data-reveal-delay="1">
                    <span data-i18n="about.title">We believe technology should feel effortless</span>
                </h2>
                <p style="font-size:1.05rem;color:#64748B;line-height:1.8;margin:1.5rem auto 3rem;max-width:40rem;"
                    data-reveal data-reveal-delay="2" data-i18n="about.desc">
                    RBeverything is a technology studio passionate about building products that are not only powerful
                    but delightful to use.
                </p>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:2rem;border:1px solid rgba(255,255,255,0.06);border-radius:1.25rem;padding:2.5rem;background:rgba(255,255,255,0.02);"
                    data-reveal data-reveal-delay="3">
                    <div>
                        <div
                            style="font-size:2.4rem;font-weight:900;letter-spacing:-0.04em;line-height:1;background:linear-gradient(135deg,#38BDF8,#818CF8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                            10+</div>
                        <div style="font-size:0.78rem;color:#475569;margin-top:0.4rem;font-weight:500;"
                            data-i18n="about.stat_products">Products shipped</div>
                    </div>
                    <div
                        style="border-left:1px solid rgba(255,255,255,0.06);border-right:1px solid rgba(255,255,255,0.06);">
                        <div
                            style="font-size:2.4rem;font-weight:900;letter-spacing:-0.04em;line-height:1;background:linear-gradient(135deg,#38BDF8,#818CF8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                            3+</div>
                        <div style="font-size:0.78rem;color:#475569;margin-top:0.4rem;font-weight:500;"
                            data-i18n="about.stat_years">Years of experience</div>
                    </div>
                    <div>
                        <div
                            style="font-size:2.4rem;font-weight:900;letter-spacing:-0.04em;line-height:1;background:linear-gradient(135deg,#38BDF8,#818CF8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                            ∞</div>
                        <div style="font-size:0.78rem;color:#475569;margin-top:0.4rem;font-weight:500;"
                            data-i18n="about.stat_quality">Commitment to quality</div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- FOOTER --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <footer class="rb-footer" id="contact" aria-label="Footer">
        <div class="rb-footer-grid">

            {{-- Col 1: Logo + slogan + social --}}
            <div>
                <span class="rb-footer-logo">RBeverything</span>
                <p class="rb-footer-slogan" data-i18n="footer.slogan">Everything you need. Smarter systems, bolder
                    results — delivered with precision and care.</p>
                <div style="display:flex;gap:0.6rem;margin-top:1.5rem;">
                    @foreach($pageData['footer']['socials'] as $social)
                        <a href="{{ $social['href'] }}" target="_blank" rel="noopener" class="rb-social-link"
                            aria-label="{{ $social['name'] }}">
                            @if($social['icon'] === 'github')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" />
                                </svg>
                            @elseif($social['icon'] === 'linkedin')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                                </svg>
                            @elseif($social['icon'] === 'instagram')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                                </svg>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Col 2: Quick Links --}}
            <div>
                <h4 class="rb-footer-col-title" data-i18n="footer.quick_links">Quick Links</h4>
                @foreach($pageData['footer']['quick_links'] as $link)
                    <a href="{{ $link['href'] }}" class="rb-footer-link" @if(isset($link['label_key']))
                    data-i18n="{{ $link['label_key'] }}" @endif>
                        {{ $link['label'] ?? $link['label_key'] ?? '' }}
                    </a>
                @endforeach
            </div>

            {{-- Col 3: Resources --}}
            <div>
                <h4 class="rb-footer-col-title" data-i18n="footer.resources">Resources</h4>
                @foreach($pageData['footer']['resources'] as $link)
                    <a href="{{ $link['href'] }}" class="rb-footer-link" @if(isset($link['label_key']))
                    data-i18n="{{ $link['label_key'] }}" @endif>
                        {{ $link['label'] ?? $link['label_key'] ?? '' }}
                    </a>
                @endforeach
            </div>

            {{-- Col 4: Contact --}}
            <div>
                <h4 class="rb-footer-col-title" data-i18n="footer.contact">Get in Touch</h4>
                <a href="mailto:{{ $pageData['footer']['email'] }}" class="rb-footer-link"
                    style="display:flex;align-items:center;gap:0.5rem;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                    {{ $pageData['footer']['email'] }}
                </a>
                <p style="font-size:0.78rem;color:#334155;line-height:1.65;margin-top:1.25rem;"
                    data-i18n="footer.based">
                    Based in Indonesia. Serving clients worldwide.
                </p>
                <a href="mailto:{{ $pageData['footer']['email'] }}" class="rb-btn-hero"
                    style="margin-top:1.5rem;display:inline-flex;font-size:0.85rem;padding:0.6rem 1.3rem;"
                    data-i18n="footer.start_project">
                    Start a Project
                </a>
            </div>

        </div>

        {{-- Bottom bar --}}
        <div class="rb-footer-bottom">
            <span>© {{ date('Y') }} RBeverything. <span data-i18n="footer.copyright">All rights reserved.</span></span>
            <div style="display:flex;gap:1.5rem;">
                <a href="#" class="rb-footer-link" style="margin:0;" data-i18n="footer.privacy">Privacy Policy</a>
                <a href="#" class="rb-footer-link" style="margin:0;" data-i18n="footer.terms">Terms of Use</a>
            </div>
        </div>
    </footer>

</body>

</html>