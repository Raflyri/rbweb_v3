<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Blog — RBeverything</title>
    <meta name="description" content="Latest insights, tutorials, and tech deep-dives from RBeverything.">
    <meta property="og:title" content="Blog — RBeverything">
    <meta property="og:description" content="Latest insights, tutorials, and tech deep-dives from RBeverything.">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900&family=JetBrains+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
    {{-- NAVIGATION --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <header id="rb-nav" class="rb-nav scrolled" role="banner">
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
                <a href="/#products" class="rb-nav-link">Products</a>
                <a href="/#services" class="rb-nav-link">Services</a>
                <a href="/blog" class="rb-nav-link" style="color:var(--color-text);">Blog</a>
                <a href="/#about" class="rb-nav-link">About Us</a>
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
            <a href="/#products"
                style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);">Products</a>
            <a href="/#services"
                style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);">Services</a>
            <a href="/blog"
                style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);">Blog</a>
            <a href="/#about"
                style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);">About Us</a>
        </nav>
        <a href="mailto:hello@rbeverything.com" class="rb-btn-hero" style="margin-top:1.25rem;width:fit-content;">
            Let's Collaborate
        </a>
    </div>

    <main style="padding-top:8rem;">

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- BLOG HEADER --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <section aria-label="Blog Header">
            <div class="rb-section" style="padding-bottom:2rem;">
                {{-- Breadcrumb --}}
                <nav style="margin-bottom:2rem;font-size:0.8rem;color:var(--color-muted);" aria-label="Breadcrumb">
                    <a href="/" style="color:var(--color-muted);text-decoration:none;transition:color 0.3s;"
                       onmouseover="this.style.color='#DC2626'" onmouseout="this.style.color=''">Home</a>
                    <span style="margin:0 0.5rem;opacity:0.4;">/</span>
                    <span style="color:var(--color-text);">Blog</span>
                </nav>

                <div style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:2rem;">
                    <div>
                        <span class="rb-section-label">Latest Insights</span>
                        <h1 class="rb-section-title" style="margin-bottom:0.5rem;">Blog</h1>
                        <p style="font-size:1.05rem;color:var(--color-muted);font-weight:300;max-width:32rem;line-height:1.7;">
                            Deep-dives into technology, tutorials, and insights from our engineering team.
                        </p>
                    </div>

                    {{-- SEARCH BAR --}}
                    <form action="{{ route('blog.index') }}" method="GET" id="blog-search-form"
                          style="display:flex;align-items:center;gap:0.5rem;width:100%;max-width:24rem;">
                        <div style="position:relative;flex:1;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--color-muted)"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);pointer-events:none;">
                                <circle cx="11" cy="11" r="8" /><path d="M21 21l-4.35-4.35" />
                            </svg>
                            <input type="text" name="search" id="blog-search-input"
                                   value="{{ $search ?? '' }}"
                                   placeholder="Search articles..."
                                   style="width:100%;padding:0.7rem 1rem 0.7rem 2.75rem;border-radius:9999px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.03);color:var(--color-text);font-size:0.875rem;font-family:inherit;outline:none;transition:border-color 0.3s,box-shadow 0.3s;"
                                   onfocus="this.style.borderColor='rgba(220,38,38,0.3)';this.style.boxShadow='0 0 0 3px rgba(220,38,38,0.08)'"
                                   onblur="this.style.borderColor='rgba(255,255,255,0.08)';this.style.boxShadow='none'">
                        </div>
                        <button type="submit" class="rb-btn-primary"
                                style="padding:0.7rem 1.25rem;font-size:0.8rem;border-radius:9999px;">
                            Search
                        </button>
                    </form>
                </div>

                @if($search)
                    <div style="margin-top:1.5rem;display:flex;align-items:center;gap:0.75rem;">
                        <p style="font-size:0.875rem;color:var(--color-muted);">
                            Showing results for "<span style="color:var(--color-text);font-weight:600;">{{ $search }}</span>"
                            <span style="opacity:0.5;">·</span>
                            {{ $articles->total() }} {{ Str::plural('article', $articles->total()) }} found
                        </p>
                        <a href="{{ route('blog.index') }}"
                           style="font-size:0.75rem;font-weight:600;color:#DC2626;text-decoration:none;padding:0.25rem 0.75rem;border-radius:9999px;border:1px solid rgba(220,38,38,0.25);transition:background 0.3s;"
                           onmouseover="this.style.background='rgba(220,38,38,0.08)'"
                           onmouseout="this.style.background='transparent'">
                            Clear
                        </a>
                    </div>
                @endif
            </div>
        </section>

        <div class="rb-divider"></div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- ARTICLES GRID --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <section aria-label="Articles">
            <div class="rb-section" style="padding-top:3rem;">

                @if($articles->count() > 0)
                    <div style="display:grid;grid-template-columns:repeat(1,1fr);gap:2rem;"
                         class="blog-grid">
                        @foreach($articles as $article)
                            @php
                                $title   = $article->getTranslation('title', $locale, true);
                                $content = $article->getTranslation('content', $locale, true);
                                $excerpt = Str::limit(strip_tags($content), 160);
                            @endphp
                            <a href="{{ route('blog.show', $article->slug) }}" class="rb-card"
                               style="display:flex;flex-direction:column;text-decoration:none;color:inherit;overflow:hidden;"
                               id="article-card-{{ $article->id }}">
                                <div class="rb-card-content" style="display:flex;flex-direction:column;height:100%;">
                                    {{-- Thumbnail --}}
                                    @if($article->thumbnail)
                                        <div style="width:100%;height:200px;overflow:hidden;">
                                            <img src="{{ asset('storage/' . $article->thumbnail) }}"
                                                 alt="{{ $title }}"
                                                 style="width:100%;height:100%;object-fit:cover;transition:transform 0.5s ease;"
                                                 onmouseover="this.style.transform='scale(1.05)'"
                                                 onmouseout="this.style.transform='scale(1)'">
                                        </div>
                                    @else
                                        <div style="width:100%;height:200px;background:linear-gradient(135deg,rgba(220,38,38,0.08),rgba(185,28,28,0.03));display:flex;align-items:center;justify-content:center;">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(220,38,38,0.2)"
                                                 stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="18" height="18" rx="2" />
                                                <circle cx="8.5" cy="8.5" r="1.5" />
                                                <polyline points="21 15 16 10 5 21" />
                                            </svg>
                                        </div>
                                    @endif

                                    {{-- Content --}}
                                    <div style="padding:1.5rem;display:flex;flex-direction:column;flex:1;gap:0.75rem;">
                                        <span style="font-size:0.65rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#DC2626;">
                                            Article
                                        </span>
                                        <h2 style="font-size:1.15rem;font-weight:700;letter-spacing:-0.015em;line-height:1.35;color:var(--color-text);margin:0;">
                                            {{ $title }}
                                        </h2>
                                        <p style="font-size:0.85rem;color:var(--color-muted);line-height:1.65;flex:1;">
                                            {{ $excerpt }}
                                        </p>
                                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:0.75rem;border-top:1px solid rgba(255,255,255,0.05);">
                                            <span style="font-size:0.75rem;color:var(--color-muted);font-family:var(--font-mono);">
                                                {{ $article->published_at?->format('M d, Y') ?? '' }}
                                            </span>
                                            <span style="font-size:0.75rem;font-weight:600;color:#DC2626;display:flex;align-items:center;gap:0.25rem;">
                                                Read more
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- PAGINATION --}}
                    @if($articles->hasPages())
                        <div style="margin-top:3rem;display:flex;justify-content:center;" id="blog-pagination">
                            {{ $articles->links() }}
                        </div>
                    @endif

                @else
                    {{-- EMPTY STATE --}}
                    <div style="text-align:center;padding:5rem 1rem;" id="blog-empty-state">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="rgba(220,38,38,0.15)"
                             stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
                             style="margin:0 auto 1.5rem;">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                        </svg>
                        <h3 style="font-size:1.25rem;font-weight:700;color:var(--color-text);margin-bottom:0.5rem;font-family:var(--font-sans, 'Inter', sans-serif);">
                            @if($search)
                                Artikel Tidak Ditemukan
                            @else
                                Artikel Belum Tersedia
                            @endif
                        </h3>
                        <p style="font-size:0.9rem;color:var(--color-muted);max-width:26rem;margin:0 auto;line-height:1.6;">
                            @if($search)
                                Kami tidak dapat menemukan artikel yang cocok dengan "{{ $search }}". Silakan coba kata kunci pencarian yang lain.
                            @else
                                Kami sedang menyiapkan berbagai wawasan dan tutorial teknologi terbaru. Silakan kembali lagi nanti.
                            @endif
                        </p>
                    </div>
                @endif

            </div>
        </section>

    </main>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- FOOTER --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
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
                <a href="/" class="rb-footer-link" style="margin:0;">Home</a>
                <a href="/blog" class="rb-footer-link" style="margin:0;">Blog</a>
            </div>
        </div>
    </footer>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- RESPONSIVE GRID + PAGINATION STYLING --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <style>
        /* Blog responsive grid */
        @media (min-width: 640px) {
            .blog-grid { grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media (min-width: 1024px) {
            .blog-grid { grid-template-columns: repeat(3, 1fr) !important; }
        }

        /* Laravel pagination dark theme */
        nav[role="navigation"] {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
        }
        nav[role="navigation"] .flex-1 { display: none; }
        nav[role="navigation"] .hidden { display: flex !important; gap: 0.25rem; }
        nav[role="navigation"] span[aria-current="page"] span,
        nav[role="navigation"] a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.25rem;
            height: 2.25rem;
            padding: 0 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.06);
        }
        nav[role="navigation"] span[aria-current="page"] span {
            background: rgba(220,38,38,0.12);
            border-color: rgba(220,38,38,0.3);
            color: #DC2626;
            font-weight: 700;
        }
        nav[role="navigation"] a {
            color: var(--color-muted);
            background: rgba(255,255,255,0.02);
        }
        nav[role="navigation"] a:hover {
            background: rgba(220,38,38,0.06);
            border-color: rgba(220,38,38,0.2);
            color: var(--color-text);
        }
        nav[role="navigation"] span.cursor-default {
            display: none;
        }
        nav[role="navigation"] p {
            font-size: 0.8rem;
            color: var(--color-muted);
        }
        /* Mobile menu toggle */
        .rb-hamburger { display: none; }
        @media (max-width: 768px) {
            .rb-hamburger { display: flex; }
            .rb-desktop-nav { display: none !important; }
        }
    </style>

    <script>
        // Simple hamburger toggle for blog pages
        document.getElementById('rb-hamburger')?.addEventListener('click', function() {
            this.classList.toggle('open');
            document.getElementById('rb-mobile-menu')?.classList.toggle('open');
        });
        // Close mobile menu on link click
        document.querySelectorAll('#rb-mobile-menu a').forEach(a => {
            a.addEventListener('click', () => {
                document.getElementById('rb-hamburger')?.classList.remove('open');
                document.getElementById('rb-mobile-menu')?.classList.remove('open');
            });
        });
    </script>

</body>

</html>
