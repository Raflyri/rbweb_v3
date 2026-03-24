<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $article->getTranslation('title', $locale, true) }} — RBeverything</title>
    <meta name="description" content="{{ Str::limit(strip_tags($article->getTranslation('content', $locale, true)), 160) }}">
    <meta property="og:title" content="{{ $article->getTranslation('title', $locale, true) }} — RBeverything">
    <meta property="og:type" content="article">
    @if($article->thumbnail)
        <meta property="og:image" content="{{ asset('storage/' . $article->thumbnail) }}">
    @endif
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
        <div style="max-width:80rem;margin:0 auto;padding:0 1.75rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;">
            <a href="/" style="text-decoration:none;flex-shrink:0;" aria-label="RBeverything — Home">
                <span style="font-size:1.3rem;font-weight:900;letter-spacing:-0.04em;background:linear-gradient(135deg,#E53E3E,#C53030);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                    RBeverything
                </span>
            </a>
            <nav class="rb-desktop-nav" style="display:flex;align-items:center;gap:2rem;" aria-label="Main navigation">
                <a href="/#products" class="rb-nav-link">Products</a>
                <a href="/#services" class="rb-nav-link">Services</a>
                <a href="/blog" class="rb-nav-link" style="color:var(--color-text);">Blog</a>
                <a href="/#about" class="rb-nav-link">About Us</a>
            </nav>
            <div class="rb-desktop-nav" style="display:flex;align-items:center;gap:0.875rem;">
                <a href="mailto:hello@rbeverything.com" class="rb-btn-primary">
                    Let's Collaborate
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
            <button id="rb-hamburger" class="rb-hamburger" aria-label="Open navigation menu" aria-expanded="false" aria-controls="rb-mobile-menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    {{-- MOBILE MENU --}}
    <div id="rb-mobile-menu" class="rb-mobile-menu" role="dialog" aria-modal="true" aria-label="Mobile navigation">
        <nav style="display:flex;flex-direction:column;gap:0.25rem;">
            <a href="/#products" style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);">Products</a>
            <a href="/#services" style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);">Services</a>
            <a href="/blog" style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);">Blog</a>
            <a href="/#about" style="font-size:1.8rem;font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;text-decoration:none;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);">About Us</a>
        </nav>
        <a href="mailto:hello@rbeverything.com" class="rb-btn-hero" style="margin-top:1.25rem;width:fit-content;">Let's Collaborate</a>
    </div>

    <main style="padding-top:8rem;">

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- ARTICLE HEADER --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <section aria-label="Article Header">
            <div style="max-width:56rem;margin:0 auto;padding:0 1.5rem 3rem;">

                {{-- Breadcrumb --}}
                <nav style="margin-bottom:2.5rem;font-size:0.8rem;color:var(--color-muted);" aria-label="Breadcrumb">
                    <a href="/" style="color:var(--color-muted);text-decoration:none;transition:color 0.3s;"
                       onmouseover="this.style.color='#DC2626'" onmouseout="this.style.color=''">Home</a>
                    <span style="margin:0 0.5rem;opacity:0.4;">/</span>
                    <a href="/blog" style="color:var(--color-muted);text-decoration:none;transition:color 0.3s;"
                       onmouseover="this.style.color='#DC2626'" onmouseout="this.style.color=''">Blog</a>
                    <span style="margin:0 0.5rem;opacity:0.4;">/</span>
                    <span style="color:var(--color-text);">{{ Str::limit($article->getTranslation('title', $locale, true), 40) }}</span>
                </nav>

                {{-- Category + Date --}}
                <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
                    <span style="font-size:0.65rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#DC2626;padding:0.3rem 0.75rem;border-radius:9999px;background:rgba(220,38,38,0.08);border:1px solid rgba(220,38,38,0.2);">
                        Article
                    </span>
                    @if($article->published_at)
                        <span style="font-size:0.8rem;color:var(--color-muted);font-family:var(--font-mono);">
                            {{ $article->published_at->format('F d, Y') }}
                        </span>
                    @endif
                    <span style="font-size:0.8rem;color:var(--color-muted);">
                        {{ ceil(str_word_count(strip_tags($article->getTranslation('content', $locale, true))) / 200) }} min read
                    </span>
                </div>

                {{-- Title --}}
                <h1 style="font-size:clamp(2rem,5vw,3.25rem);font-weight:900;letter-spacing:-0.03em;line-height:1.1;color:var(--color-text);margin-bottom:1.5rem;">
                    {{ $article->getTranslation('title', $locale, true) }}
                </h1>

                {{-- Excerpt / Lead --}}
                @php
                    $content = $article->getTranslation('content', $locale, true);
                    $excerpt  = Str::limit(strip_tags($content), 180);
                @endphp
                <p style="font-size:1.15rem;color:var(--color-muted);font-weight:300;line-height:1.75;margin-bottom:2.5rem;">
                    {{ $excerpt }}
                </p>

                {{-- Divider --}}
                <div style="height:1px;background:linear-gradient(to right,transparent,rgba(220,38,38,0.15),transparent);margin-bottom:2.5rem;"></div>
            </div>
        </section>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- THUMBNAIL --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        @if($article->thumbnail)
            <section aria-label="Article Thumbnail">
                <div style="max-width:56rem;margin:0 auto;padding:0 1.5rem 3rem;">
                    <div style="border-radius:1.25rem;overflow:hidden;border:1px solid rgba(255,255,255,0.06);box-shadow:0 20px 60px rgba(0,0,0,0.5);">
                        <img src="{{ asset('storage/' . $article->thumbnail) }}"
                             alt="{{ $article->getTranslation('title', $locale, true) }}"
                             style="width:100%;height:auto;display:block;max-height:480px;object-fit:cover;">
                    </div>
                </div>
            </section>
        @endif

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- ARTICLE CONTENT (prose) --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <section aria-label="Article Content">
            <div style="max-width:56rem;margin:0 auto;padding:0 1.5rem 6rem;">
                <div class="prose prose-invert prose-lg"
                     style="max-width:none;color:var(--color-text);">
                    {!! $article->getTranslation('content', $locale, true) !!}
                </div>
            </div>
        </section>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- RELATED ARTICLES --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        @if($related->count() > 0)
            <section aria-label="Related Articles">
                <div style="max-width:80rem;margin:0 auto;padding:4rem 1.5rem;">
                    <div style="height:1px;background:linear-gradient(to right,transparent,rgba(220,38,38,0.1),transparent);margin-bottom:4rem;"></div>

                    <div style="margin-bottom:2.5rem;">
                        <span class="rb-section-label">Continue Reading</span>
                        <h2 style="font-size:clamp(1.4rem,3vw,2rem);font-weight:800;letter-spacing:-0.02em;color:var(--color-text);margin-top:0.5rem;">
                            More from our Blog
                        </h2>
                    </div>

                    <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(1,1fr);" class="related-grid">
                        @foreach($related as $rel)
                            @php
                                $relTitle   = $rel->getTranslation('title', $locale, true);
                                $relContent = $rel->getTranslation('content', $locale, true);
                            @endphp
                            <a href="{{ route('blog.show', $rel->slug) }}" class="rb-card"
                               style="display:flex;flex-direction:column;text-decoration:none;color:inherit;overflow:hidden;">
                                <div class="rb-card-content" style="display:flex;flex-direction:column;height:100%;">
                                    @if($rel->thumbnail)
                                        <div style="height:160px;overflow:hidden;">
                                            <img src="{{ asset('storage/' . $rel->thumbnail) }}"
                                                 alt="{{ $relTitle }}"
                                                 style="width:100%;height:100%;object-fit:cover;transition:transform 0.5s;"
                                                 onmouseover="this.style.transform='scale(1.05)'"
                                                 onmouseout="this.style.transform='scale(1)'">
                                        </div>
                                    @else
                                        <div style="height:160px;background:linear-gradient(135deg,rgba(220,38,38,0.06),rgba(185,28,28,0.02));display:flex;align-items:center;justify-content:center;">
                                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(220,38,38,0.15)" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        </div>
                                    @endif
                                    <div style="padding:1.25rem;display:flex;flex-direction:column;gap:0.5rem;flex:1;">
                                        <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#DC2626;">Article</span>
                                        <h3 style="font-size:1rem;font-weight:700;line-height:1.4;color:var(--color-text);margin:0;">{{ $relTitle }}</h3>
                                        <p style="font-size:0.8rem;color:var(--color-muted);line-height:1.6;flex:1;margin:0;">{{ Str::limit(strip_tags($relContent), 100) }}</p>
                                        <span style="font-size:0.7rem;color:var(--color-muted);font-family:var(--font-mono);margin-top:0.5rem;">
                                            {{ $rel->published_at?->format('M d, Y') ?? '' }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Back to Blog --}}
        <div style="max-width:56rem;margin:0 auto;padding:0 1.5rem 5rem;text-align:center;">
            <a href="/blog" class="rb-btn-ghost"
               style="display:inline-flex;align-items:center;gap:0.5rem;font-size:0.9rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back to Blog
            </a>
        </div>

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
    {{-- TYPOGRAPHY OVERRIDES FOR DARK THEME --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <style>
        /* Prose / Typography dark overrides */
        .prose-invert {
            --tw-prose-body:        #C9D1D9;
            --tw-prose-headings:    #F1F5F9;
            --tw-prose-lead:        #94A3B8;
            --tw-prose-links:       #DC2626;
            --tw-prose-bold:        #F1F5F9;
            --tw-prose-counters:    #64748B;
            --tw-prose-bullets:     #DC2626;
            --tw-prose-hr:          rgba(255,255,255,0.08);
            --tw-prose-quotes:      #F1F5F9;
            --tw-prose-quote-borders: rgba(220,38,38,0.4);
            --tw-prose-captions:    #64748B;
            --tw-prose-code:        #C9D1D9;
            --tw-prose-pre-code:    #C9D1D9;
            --tw-prose-pre-bg:      rgba(255,255,255,0.03);
            --tw-prose-th-borders:  rgba(255,255,255,0.08);
            --tw-prose-td-borders:  rgba(255,255,255,0.05);
        }
        .prose h1,.prose h2,.prose h3,.prose h4 {
            letter-spacing: -0.02em;
            font-weight: 800;
        }
        .prose a { transition: opacity 0.2s; }
        .prose a:hover { opacity: 0.8; }
        .prose pre {
            background: rgba(255,255,255,0.03) !important;
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 0.75rem;
        }
        .prose code:not(pre code) {
            background: rgba(220,38,38,0.08);
            border: 1px solid rgba(220,38,38,0.15);
            border-radius: 0.25rem;
            padding: 0.15em 0.4em;
            font-size: 0.85em;
            color: #FCA5A5;
        }
        .prose blockquote {
            border-left-color: rgba(220,38,38,0.4) !important;
            background: rgba(220,38,38,0.03);
            border-radius: 0 0.5rem 0.5rem 0;
            padding: 1rem 1.5rem;
        }
        .prose img { border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.06); }
        .prose table { border-collapse: collapse; }
        .prose thead { background: rgba(255,255,255,0.02); }

        /* Related articles responsive grid */
        @media (min-width: 640px) {
            .related-grid { grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media (min-width: 1024px) {
            .related-grid { grid-template-columns: repeat(3, 1fr) !important; }
        }

        /* Mobile nav */
        .rb-hamburger { display: none; }
        @media (max-width: 768px) {
            .rb-hamburger { display: flex; }
            .rb-desktop-nav { display: none !important; }
        }
    </style>

    <script>
        document.getElementById('rb-hamburger')?.addEventListener('click', function() {
            this.classList.toggle('open');
            document.getElementById('rb-mobile-menu')?.classList.toggle('open');
        });
        document.querySelectorAll('#rb-mobile-menu a').forEach(a => {
            a.addEventListener('click', () => {
                document.getElementById('rb-hamburger')?.classList.remove('open');
                document.getElementById('rb-mobile-menu')?.classList.remove('open');
            });
        });
    </script>

</body>

</html>
