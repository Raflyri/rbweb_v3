<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ══════════════════════════════════════════════════════
         SEO — Title
         Child views: @section('meta_title', 'Your Page Title')
    ══════════════════════════════════════════════════════ --}}
    <title>@yield('meta_title', 'RBeverything') — RBeverything</title>

    {{-- ══════════════════════════════════════════════════════
         SEO — Meta Description
         Child views: @section('meta_description', 'Your description…')
    ══════════════════════════════════════════════════════ --}}
    <meta name="description"
          content="@yield('meta_description', 'Smarter systems, bolder results — insights, tutorials, and tech deep-dives from RBeverything.')">

    {{-- ══════════════════════════════════════════════════════
         SEO — Canonical URL
         Child views: @section('canonical', url()->current())
    ══════════════════════════════════════════════════════ --}}
    <link rel="canonical" href="@yield('canonical', url()->current())">

    {{-- ══════════════════════════════════════════════════════
         SEO — Robots (override per-page if needed)
         Child views: @section('meta_robots', 'noindex, nofollow')
    ══════════════════════════════════════════════════════ --}}
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">

    {{-- ══════════════════════════════════════════════════════
         Open Graph tags
         Child views can override each @section below.
    ══════════════════════════════════════════════════════ --}}
    <meta property="og:site_name" content="RBeverything">
    <meta property="og:type"      content="@yield('og_type', 'website')">
    <meta property="og:url"       content="@yield('canonical', url()->current())">
    <meta property="og:locale"    content="{{ str_replace('-', '_', app()->getLocale()) }}">

    <meta property="og:title"
          content="@hasSection('og_title')@yield('og_title')@elseif(View::hasSection('meta_title'))@yield('meta_title')@else RBeverything @endif">

    <meta property="og:description"
          content="@hasSection('og_description')@yield('og_description')@elseif(View::hasSection('meta_description'))@yield('meta_description')@else Smarter systems, bolder results — insights, tutorials, and tech deep-dives from RBeverything. @endif">

    {{-- og:image — defaults to /og-default.png if no child section provided --}}
    <meta property="og:image"
          content="@yield('og_image', asset('images/og-default.png'))">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt"
          content="@yield('og_title', 'RBeverything')">

    {{-- ══════════════════════════════════════════════════════
         Twitter / X Card tags
    ══════════════════════════════════════════════════════ --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@rbeverything">
    <meta name="twitter:title"
          content="@hasSection('og_title')@yield('og_title')@elseif(View::hasSection('meta_title'))@yield('meta_title')@else RBeverything @endif">
    <meta name="twitter:description"
          content="@hasSection('og_description')@yield('og_description')@elseif(View::hasSection('meta_description'))@yield('meta_description')@else Smarter systems, bolder results — insights, tutorials, and tech deep-dives from RBeverything. @endif">
    <meta name="twitter:image"
          content="@yield('og_image', asset('images/og-default.png'))">

    {{-- ══════════════════════════════════════════════════════
         Extra <head> content (JSON-LD, hreflang, etc.)
         Child views: @section('head_extra') … @endsection
    ══════════════════════════════════════════════════════ --}}
    @yield('head_extra')

    {{-- ══════════════════════════════════════════════════════
         Google Fonts
    ══════════════════════════════════════════════════════ --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900&family=JetBrains+Mono:wght@400;700&display=swap"
          rel="stylesheet">

    {{-- ══════════════════════════════════════════════════════
         Vite — CSS + JS (includes Tailwind v4 + Typography plugin)
    ══════════════════════════════════════════════════════ --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    {{-- ════════════════════════════════════════════════════════════
         ANIMATED BACKGROUND (shared across all public pages)
    ════════════════════════════════════════════════════════════ --}}
    <div class="rb-bg" aria-hidden="true">
        <div class="rb-bg-gradient"></div>
        <div class="rb-dot-grid"></div>
        <div class="rb-binary-overlay"></div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         NAVIGATION
    ════════════════════════════════════════════════════════════ --}}
    <header id="rb-nav" class="rb-nav scrolled" role="banner">
        <div style="max-width:80rem;margin:0 auto;padding:0 1.75rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;">

            {{-- Logo --}}
            <a href="/" style="text-decoration:none;flex-shrink:0;" aria-label="RBeverything — Home">
                <span style="font-size:1.3rem;font-weight:900;letter-spacing:-0.04em;background:linear-gradient(135deg,#E53E3E,#C53030);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                    RBeverything
                </span>
            </a>

            {{-- Desktop Nav --}}
            <nav class="rb-desktop-nav" style="display:flex;align-items:center;gap:2rem;" aria-label="Main navigation">
                <a href="/#products" class="rb-nav-link">Products</a>
                <a href="/#services" class="rb-nav-link">Services</a>
                <a href="/blog"      class="rb-nav-link @yield('nav_blog_active')">Blog</a>
                <a href="/#about"    class="rb-nav-link">About Us</a>
            </nav>

            {{-- CTA --}}
            <div class="rb-desktop-nav" style="display:flex;align-items:center;gap:0.875rem;">
                <a href="mailto:hello@rbeverything.com" class="rb-btn-primary">
                    Let's Collaborate
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            {{-- Hamburger --}}
            <button id="rb-hamburger" class="rb-hamburger"
                    aria-label="Open navigation menu" aria-expanded="false" aria-controls="rb-mobile-menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    {{-- Mobile Menu --}}
    <div id="rb-mobile-menu" class="rb-mobile-menu" role="dialog" aria-modal="true" aria-label="Mobile navigation">
        <nav style="display:flex;flex-direction:column;gap:0.25rem;">
            <a href="/#products" style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);">Products</a>
            <a href="/#services" style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);">Services</a>
            <a href="/blog"      style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);">Blog</a>
            <a href="/#about"    style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);">About Us</a>
        </nav>
        <a href="mailto:hello@rbeverything.com" class="rb-btn-hero" style="margin-top:1.25rem;width:fit-content;">
            Let's Collaborate
        </a>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         PAGE CONTENT
         Child views: @section('content') … @endsection
    ════════════════════════════════════════════════════════════ --}}
    <main style="@yield('main_style', 'padding-top:8rem;')">
        @yield('content')
    </main>

    {{-- ════════════════════════════════════════════════════════════
         FOOTER
    ════════════════════════════════════════════════════════════ --}}
    <footer class="rb-footer" aria-label="Footer">
        <div style="max-width:80rem;margin:0 auto;text-align:center;">
            <a href="/" style="text-decoration:none;">
                <span class="rb-footer-logo">RBeverything</span>
            </a>
            <p style="font-size:0.85rem;color:var(--color-muted);margin-top:0.75rem;line-height:1.6;">
                Everything you need. Smarter systems, bolder results.
            </p>
        </div>
        <div class="rb-footer-bottom">
            <span>© {{ date('Y') }} RBeverything. All rights reserved.</span>
            <div style="display:flex;gap:1.5rem;">
                <a href="/"    class="rb-footer-link" style="margin:0;">Home</a>
                <a href="/blog" class="rb-footer-link" style="margin:0;">Blog</a>
            </div>
        </div>
    </footer>

    {{-- ════════════════════════════════════════════════════════════
         SHARED MOBILE NAV SCRIPT
    ════════════════════════════════════════════════════════════ --}}
    <script>
        document.getElementById('rb-hamburger')?.addEventListener('click', function () {
            this.classList.toggle('open');
            const menu = document.getElementById('rb-mobile-menu');
            menu?.classList.toggle('open');
            this.setAttribute('aria-expanded', menu?.classList.contains('open') ? 'true' : 'false');
        });
        document.querySelectorAll('#rb-mobile-menu a').forEach(a => {
            a.addEventListener('click', () => {
                document.getElementById('rb-hamburger')?.classList.remove('open');
                document.getElementById('rb-mobile-menu')?.classList.remove('open');
            });
        });
    </script>

    {{-- ════════════════════════════════════════════════════════════
         Extra scripts / inline styles injected by child views
         Child views: @section('scripts') … @endsection
                       @section('styles')  … @endsection
    ════════════════════════════════════════════════════════════ --}}
    @yield('styles')
    @yield('scripts')

</body>

</html>
