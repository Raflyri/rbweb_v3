@extends('layouts.public')

{{-- ════════════════════════════════════════════════════════════
     SEO — Article show page (100% dynamic from DB)
════════════════════════════════════════════════════════════ --}}
@php
    $locale = app()->getLocale();

    /* ── Translatable SEO fields ─────────────────────────── */
    $seoTitle       = $article->getTranslation('meta_title',       $locale, false)
                   ?: $article->getTranslation('title',            $locale, true);

    $seoDescription = $article->getTranslation('meta_description', $locale, false)
                   ?: Str::limit(strip_tags(
                          $article->getTranslation('content',      $locale, true)
                      ), 160);

    $ogImage = $article->thumbnail
             ? asset('storage/' . $article->thumbnail)
             : asset('images/og-default.png');

    /* ── Article display fields ──────────────────────────── */
    $displayTitle   = $article->getTranslation('title',   $locale, true);
    $displayContent = $article->getTranslation('content', $locale, true);
    $displayExcerpt = $article->getTranslation('excerpt', $locale, false)
                   ?: Str::limit(strip_tags($displayContent), 200);

    /* ── Meta ────────────────────────────────────────────── */
    $wordCount   = str_word_count(strip_tags($displayContent));
    $readMinutes = max(1, (int) ceil($wordCount / 200));
    $authorName  = $article->user?->name ?? 'RBeverything';
@endphp

@section('meta_title',       $seoTitle)
@section('meta_description', $seoDescription)
@section('canonical',        url()->current())
@section('og_type',          'article')
@section('og_title',         $seoTitle . ' — RBeverything')
@section('og_description',   $seoDescription)
@section('og_image',         $ogImage)
@section('nav_blog_active',  'style="color:var(--color-text);"')
@section('main_style',       'padding-top:0;overflow-x:hidden;')

{{-- ════════════════════════════════════════════════════════════
     HEAD EXTRAS — Alpine.js CDN + JSON-LD
════════════════════════════════════════════════════════════ --}}
@section('head_extra')
    {{-- Alpine.js — loaded only on this page --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Article JSON-LD structured data --}}
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Article",
      "headline": "{{ addslashes($displayTitle) }}",
      "description": "{{ addslashes($seoDescription) }}",
      "image": ["{{ $ogImage }}"],
      "datePublished": "{{ $article->published_at?->toIso8601String() ?? '' }}",
      "dateModified":  "{{ $article->updated_at?->toIso8601String()  ?? '' }}",
      "url": "{{ url()->current() }}",
      "wordCount": {{ $wordCount }},
      "author": [{
        "@@type": "{{ $article->user ? 'Person' : 'Organization' }}",
        "name": "{{ addslashes($authorName) }}"
      }],
      "publisher": {
        "@@type": "Organization",
        "name": "RBeverything",
        "url": "{{ url('/') }}"
      }
    }
    </script>
@endsection

{{-- ════════════════════════════════════════════════════════════
     CONTENT
════════════════════════════════════════════════════════════ --}}
@section('content')

    {{-- ════════════════════════════════════════════════════
         READING PROGRESS BAR (Alpine.js)
         Fixed thin line at very top of viewport.
         z-index 9999 so it sits above the sticky nav.
    ════════════════════════════════════════════════════ --}}
    <div
        x-data="{
            progress: 0,
            onScroll() {
                const el   = document.getElementById('article-body');
                if (!el) return;
                const rect   = el.getBoundingClientRect();
                const start  = el.offsetTop;
                const height = el.scrollHeight;
                const scrolled = window.scrollY - start + window.innerHeight * 0.15;
                this.progress = Math.min(100, Math.max(0, (scrolled / height) * 100));
            }
        }"
        x-init="window.addEventListener('scroll', () => onScroll(), { passive: true })"
        class="read-progress-wrap"
        role="progressbar"
        :aria-valuenow="Math.round(progress)"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-label="Reading progress">
        <div class="read-progress-bar" :style="'width:' + progress + '%'"></div>
    </div>

    {{-- ════════════════════════════════════════════════════
         HERO — Full-width cover image
    ════════════════════════════════════════════════════ --}}
    @if($article->thumbnail)
        <div class="article-hero" aria-hidden="true">
            <img src="{{ asset('storage/' . $article->thumbnail) }}"
                 alt="{{ $displayTitle }}"
                 class="article-hero__img">
            <div class="article-hero__overlay"></div>
        </div>
    @else
        <div class="article-hero article-hero--placeholder" aria-hidden="true">
            <div class="article-hero__placeholder-grid"></div>
            <div class="article-hero__overlay"></div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════
         ARTICLE HEADER — centred, floats over hero
    ════════════════════════════════════════════════════ --}}
    <div class="article-header-wrap">
        <header class="article-header">

            {{-- Breadcrumb --}}
            <nav class="article-breadcrumb" aria-label="Breadcrumb">
                <a href="/" class="article-breadcrumb__link">Home</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
                <a href="/blog" class="article-breadcrumb__link">Blog</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
                <span class="article-breadcrumb__current">{{ Str::limit($displayTitle, 38) }}</span>
            </nav>

            {{-- Tags --}}
            @if($article->tags->count() > 0)
                <div class="article-header__tags">
                    @foreach($article->tags as $tag)
                        <span class="article-header__tag">
                            {{ $tag->getTranslation('name', $locale, false) ?: $tag->name }}
                        </span>
                    @endforeach
                </div>
            @else
                <div class="article-header__tags">
                    <span class="article-header__tag">Article</span>
                </div>
            @endif

            {{-- Title --}}
            <h1 class="article-header__title">{{ $displayTitle }}</h1>

            {{-- Lead / excerpt --}}
            <p class="article-header__lead">{{ $displayExcerpt }}</p>

            {{-- Meta row — Author · Read time · Date --}}
            <div class="article-header__meta">
                {{-- Author avatar --}}
                <div class="article-meta-author">
                    <div class="article-meta-author__avatar" aria-hidden="true">
                        {{ strtoupper(mb_substr($authorName, 0, 1)) }}
                    </div>
                    <div class="article-meta-author__info">
                        <span class="article-meta-author__name">{{ $authorName }}</span>
                        <span class="article-meta-author__role">Author</span>
                    </div>
                </div>

                <div class="article-meta-divider" aria-hidden="true"></div>

                {{-- Read time --}}
                <div class="article-meta-stat">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span>{{ $readMinutes }} min read</span>
                </div>

                <div class="article-meta-dot" aria-hidden="true">·</div>

                {{-- Date --}}
                @if($article->published_at)
                    <time class="article-meta-date"
                          datetime="{{ $article->published_at->toIso8601String() }}">
                        {{ $article->published_at->format('F d, Y') }}
                    </time>
                @endif

                <div class="article-meta-dot" aria-hidden="true">·</div>

                {{-- Word count --}}
                <span class="article-meta-stat">
                    {{ number_format($wordCount) }} words
                </span>
            </div>

            {{-- Decorative separator --}}
            <div class="article-header__sep" aria-hidden="true"></div>

        </header>
    </div>

    {{-- ════════════════════════════════════════════════════
         ARTICLE BODY — Tailwind Typography prose
    ════════════════════════════════════════════════════ --}}
    <div class="article-body-wrap" id="article-body">
        <div class="prose prose-lg max-w-3xl mx-auto prose-invert article-prose">
            {!! $displayContent !!}
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         ARTICLE FOOTER — Share + Back
    ════════════════════════════════════════════════════ --}}
    <div class="article-footer-wrap">
        <div class="article-footer">

            {{-- Share row --}}
            <div class="article-share">
                <span class="article-share__label">Share this article</span>
                <div class="article-share__btns">
                    {{-- Twitter / X --}}
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($displayTitle) }}"
                       target="_blank" rel="noopener noreferrer"
                       class="article-share__btn" aria-label="Share on X (Twitter)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L2.018 2.25H8.08l4.259 5.63z"/>
                        </svg>
                    </a>
                    {{-- LinkedIn --}}
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
                       target="_blank" rel="noopener noreferrer"
                       class="article-share__btn" aria-label="Share on LinkedIn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/>
                            <rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>
                        </svg>
                    </a>
                    {{-- Copy link --}}
                    <button class="article-share__btn" aria-label="Copy link"
                            onclick="navigator.clipboard.writeText('{{ url()->current() }}').then(()=>{ this.classList.add('copied'); setTimeout(()=>this.classList.remove('copied'), 2000) })">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="article-footer__sep" aria-hidden="true"></div>

            {{-- Back to Blog --}}
            <a href="/blog" class="article-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Blog
            </a>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         RELATED ARTICLES
    ════════════════════════════════════════════════════ --}}
    @if($related->count() > 0)
        <section class="related-section" aria-label="Related Articles">
            <div class="rb-section" style="padding-top:0;">
                <div class="related-header">
                    <span class="rb-section-label">Continue Reading</span>
                    <h2 class="related-header__title">More from our Blog</h2>
                </div>

                <div class="related-grid">
                    @foreach($related as $rel)
                        @php
                            $relLocale  = $locale;
                            $relTitle   = $rel->getTranslation('title',   $relLocale, true);
                            $relContent = $rel->getTranslation('content', $relLocale, true);
                            $relExcerpt = $rel->getTranslation('excerpt', $relLocale, false)
                                       ?: Str::limit(strip_tags($relContent), 110);
                            $relSlug    = $rel->getTranslation('slug',    $relLocale, true);
                        @endphp
                        <a href="{{ route('blog.show', $relSlug) }}"
                           class="related-card" aria-label="{{ $relTitle }}">
                            {{-- Thumbnail --}}
                            @if($rel->thumbnail)
                                <div class="related-card__cover">
                                    <img src="{{ asset('storage/' . $rel->thumbnail) }}"
                                         alt="{{ $relTitle }}"
                                         class="related-card__img" loading="lazy">
                                </div>
                            @else
                                <div class="related-card__cover related-card__cover--placeholder">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none"
                                         stroke="rgba(220,38,38,0.2)" stroke-width="1"
                                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </div>
                            @endif
                            {{-- Body --}}
                            <div class="related-card__body">
                                <span class="related-card__tag">Article</span>
                                <h3 class="related-card__title">{{ $relTitle }}</h3>
                                <p class="related-card__excerpt">{{ $relExcerpt }}</p>
                                @if($rel->published_at)
                                    <time class="related-card__date"
                                          datetime="{{ $rel->published_at->toIso8601String() }}">
                                        {{ $rel->published_at->format('M d, Y') }}
                                    </time>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection

{{-- ════════════════════════════════════════════════════════════
     PAGE-SPECIFIC STYLES
════════════════════════════════════════════════════════════ --}}
@section('styles')
<style>
/* ══════════════════════════════════════════════════════
   READING PROGRESS BAR
══════════════════════════════════════════════════════ */
.read-progress-wrap {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    z-index: 9999;
    background: rgba(255,255,255,0.04);
    pointer-events: none;
}
.read-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #DC2626, #EF4444, #F87171);
    border-radius: 0 2px 2px 0;
    transition: width 0.1s linear;
    box-shadow: 0 0 8px rgba(220,38,38,0.6);
}

/* ══════════════════════════════════════════════════════
   ARTICLE HERO
══════════════════════════════════════════════════════ */
.article-hero {
    position: relative;
    width: 100%;
    height: clamp(320px, 50vw, 560px);
    overflow: hidden;
    margin-top: -8rem; /* pull up behind sticky nav */
}
.article-hero__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}
.article-hero__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to bottom,
        rgba(3,5,8,0.3)  0%,
        rgba(3,5,8,0.15) 40%,
        rgba(3,5,8,0.8)  80%,
        rgba(3,5,8,1)    100%
    );
}
/* Placeholder hero (no thumbnail) */
.article-hero--placeholder {
    background: linear-gradient(135deg, rgba(220,38,38,0.06), rgba(3,5,8,1));
}
.article-hero__placeholder-grid {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle, rgba(220,38,38,0.06) 1px, transparent 1px);
    background-size: 28px 28px;
    mask-image: radial-gradient(ellipse 100% 100% at center, black 20%, transparent 80%);
}

/* ══════════════════════════════════════════════════════
   ARTICLE HEADER (glass card, centred)
══════════════════════════════════════════════════════ */
.article-header-wrap {
    display: flex;
    justify-content: center;
    padding: 0 1.5rem;
    margin-top: -6rem; /* overlap hero bottom */
    position: relative;
    z-index: 10;
}
.article-header {
    width: 100%;
    max-width: 52rem;
    background: rgba(3,5,8,0.72);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 1.5rem;
    padding: clamp(2rem, 5vw, 3rem) clamp(1.5rem, 5vw, 3.5rem);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    text-align: center;
    box-shadow: 0 24px 80px rgba(0,0,0,0.6);
}

/* Breadcrumb */
.article-breadcrumb {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    color: var(--color-muted);
    margin-bottom: 1.5rem;
}
.article-breadcrumb svg { opacity: 0.35; flex-shrink: 0; }
.article-breadcrumb__link {
    color: var(--color-muted);
    text-decoration: none;
    transition: color 0.25s;
}
.article-breadcrumb__link:hover { color: #DC2626; }
.article-breadcrumb__current {
    color: var(--color-text);
    opacity: 0.7;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 18rem;
}

/* Tags */
.article-header__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    justify-content: center;
    margin-bottom: 1.25rem;
}
.article-header__tag {
    display: inline-block;
    padding: 0.22rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    background: rgba(220,38,38,0.1);
    color: #DC2626;
    border: 1px solid rgba(220,38,38,0.22);
}

/* Title */
.article-header__title {
    font-size: clamp(1.9rem, 5vw, 3rem);
    font-weight: 900;
    letter-spacing: -0.03em;
    line-height: 1.12;
    color: var(--color-text);
    margin: 0 0 1.25rem;
}

/* Lead */
.article-header__lead {
    font-size: 1.05rem;
    color: var(--color-muted);
    font-weight: 300;
    line-height: 1.75;
    margin: 0 0 2rem;
    max-width: 36rem;
    margin-left: auto;
    margin-right: auto;
}

/* Meta row */
.article-header__meta {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.75rem 1rem;
}
.article-meta-author {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.article-meta-author__avatar {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(220,38,38,0.25), rgba(185,28,28,0.12));
    border: 1.5px solid rgba(220,38,38,0.28);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 800;
    color: #DC2626;
    flex-shrink: 0;
}
.article-meta-author__info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0;
}
.article-meta-author__name {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--color-text);
    line-height: 1.2;
}
.article-meta-author__role {
    font-size: 0.68rem;
    color: var(--color-muted);
    line-height: 1.2;
}
.article-meta-divider {
    width: 1px;
    height: 2rem;
    background: rgba(255,255,255,0.08);
    align-self: center;
}
.article-meta-stat {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.78rem;
    color: var(--color-muted);
    font-family: var(--font-mono);
}
.article-meta-dot {
    font-size: 0.75rem;
    color: var(--color-muted);
    opacity: 0.4;
}
.article-meta-date {
    font-size: 0.78rem;
    color: var(--color-muted);
    font-family: var(--font-mono);
}

/* Separator */
.article-header__sep {
    height: 1px;
    background: linear-gradient(to right, transparent, rgba(220,38,38,0.2), transparent);
    margin-top: 2rem;
    margin-bottom: 0;
}

/* ══════════════════════════════════════════════════════
   ARTICLE BODY — prose content
══════════════════════════════════════════════════════ */
.article-body-wrap {
    padding: 4rem 1.5rem 5rem;
}

/* ── Tailwind Typography dark overrides ─────────────── */
.article-prose {
    --tw-prose-body:          #C9D1D9;
    --tw-prose-headings:      #F1F5F9;
    --tw-prose-lead:          #94A3B8;
    --tw-prose-links:         #DC2626;
    --tw-prose-bold:          #F1F5F9;
    --tw-prose-counters:      #64748B;
    --tw-prose-bullets:       #DC2626;
    --tw-prose-hr:            rgba(255,255,255,0.07);
    --tw-prose-quotes:        #F1F5F9;
    --tw-prose-quote-borders: rgba(220,38,38,0.45);
    --tw-prose-captions:      #64748B;
    --tw-prose-code:          #C9D1D9;
    --tw-prose-pre-code:      #C9D1D9;
    --tw-prose-pre-bg:        rgba(255,255,255,0.03);
    --tw-prose-th-borders:    rgba(255,255,255,0.08);
    --tw-prose-td-borders:    rgba(255,255,255,0.05);
}

/* Enhanced prose styling */
.article-prose h1,
.article-prose h2,
.article-prose h3,
.article-prose h4 {
    letter-spacing: -0.022em;
    font-weight: 800;
    scroll-margin-top: 6rem;
}
.article-prose h2 {
    border-top: 1px solid rgba(255,255,255,0.06);
    padding-top: 2rem;
    margin-top: 3rem !important;
}
.article-prose a {
    text-underline-offset: 3px;
    transition: opacity 0.2s, color 0.2s;
}
.article-prose a:hover { opacity: 0.75; }
.article-prose pre {
    background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 1rem;
    padding: 1.5rem !important;
}
.article-prose code:not(pre code) {
    background: rgba(220,38,38,0.08);
    border: 1px solid rgba(220,38,38,0.15);
    border-radius: 0.3rem;
    padding: 0.12em 0.4em;
    font-size: 0.85em;
    color: #FCA5A5;
    font-weight: 500;
}
.article-prose blockquote {
    border-left-color: rgba(220,38,38,0.5) !important;
    background: rgba(220,38,38,0.04);
    border-radius: 0 0.75rem 0.75rem 0;
    padding: 1.25rem 1.75rem;
    font-style: normal;
}
.article-prose blockquote p:first-of-type::before,
.article-prose blockquote p:last-of-type::after { content: ''; }
.article-prose img {
    border-radius: 1rem;
    border: 1px solid rgba(255,255,255,0.06);
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    width: 100%;
}
.article-prose table { border-collapse: collapse; width: 100%; }
.article-prose thead { background: rgba(255,255,255,0.03); }
.article-prose th { font-weight: 700; }
.article-prose tr { border-bottom: 1px solid rgba(255,255,255,0.05); }
.article-prose hr {
    border: none;
    height: 1px;
    background: linear-gradient(to right, transparent, rgba(220,38,38,0.2), transparent);
    margin: 3rem auto;
}

/* ══════════════════════════════════════════════════════
   ARTICLE FOOTER (share + back)
══════════════════════════════════════════════════════ */
.article-footer-wrap {
    display: flex;
    justify-content: center;
    padding: 0 1.5rem 5rem;
}
.article-footer {
    width: 100%;
    max-width: 48rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}
.article-footer__sep {
    height: 1px;
    background: linear-gradient(to right, transparent, rgba(255,255,255,0.07), transparent);
}

/* Share */
.article-share {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}
.article-share__label {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--color-muted);
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.article-share__btns {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.article-share__btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.09);
    background: rgba(255,255,255,0.03);
    color: var(--color-muted);
    text-decoration: none;
    cursor: pointer;
    transition: border-color 0.25s, background 0.25s, color 0.25s, box-shadow 0.25s;
}
.article-share__btn:hover {
    border-color: rgba(220,38,38,0.3);
    background: rgba(220,38,38,0.07);
    color: #DC2626;
    box-shadow: 0 0 16px rgba(220,38,38,0.1);
}
.article-share__btn.copied {
    border-color: rgba(52,211,153,0.4);
    background: rgba(52,211,153,0.06);
    color: #34D399;
}

/* Back link */
.article-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--color-muted);
    text-decoration: none;
    padding: 0.65rem 1.5rem;
    border-radius: 9999px;
    border: 1px solid rgba(255,255,255,0.07);
    background: rgba(255,255,255,0.02);
    align-self: flex-start;
    transition: color 0.25s, border-color 0.25s, background 0.25s;
}
.article-back:hover {
    color: var(--color-text);
    border-color: rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.05);
}
.article-back svg {
    transition: transform 0.25s;
}
.article-back:hover svg { transform: translateX(-3px); }

/* ══════════════════════════════════════════════════════
   RELATED ARTICLES SECTION
══════════════════════════════════════════════════════ */
.related-section {
    border-top: 1px solid rgba(255,255,255,0.05);
    background: rgba(255,255,255,0.01);
    padding-bottom: 5rem;
}
.related-header {
    margin-bottom: 2.5rem;
}
.related-header__title {
    font-size: clamp(1.4rem,3vw,2rem);
    font-weight: 800;
    letter-spacing: -0.022em;
    color: var(--color-text);
    margin-top: 0.5rem;
    margin-bottom: 0;
}

/* Related grid */
.related-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 1.5rem;
}
@media (min-width: 640px)  { .related-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .related-grid { grid-template-columns: repeat(3, 1fr); } }

/* Related card */
.related-card {
    display: flex;
    flex-direction: column;
    background: rgba(255,255,255,0.025);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 1.25rem;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    transition:
        border-color 0.3s cubic-bezier(0.4,0,0.2,1),
        transform    0.3s cubic-bezier(0.4,0,0.2,1),
        box-shadow   0.3s cubic-bezier(0.4,0,0.2,1);
}
.related-card:hover {
    border-color: rgba(220,38,38,0.25);
    transform: translateY(-4px);
    box-shadow: 0 16px 48px rgba(0,0,0,0.4);
}
.related-card__cover {
    aspect-ratio: 16/9;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(220,38,38,0.06), rgba(3,5,8,0.8));
}
.related-card__cover--placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
}
.related-card__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}
.related-card:hover .related-card__img { transform: scale(1.05); }
.related-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 1.25rem;
    flex: 1;
}
.related-card__tag {
    font-size: 0.58rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #DC2626;
}
.related-card__title {
    font-size: 0.95rem;
    font-weight: 700;
    letter-spacing: -0.015em;
    line-height: 1.4;
    color: var(--color-text);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.related-card__excerpt {
    font-size: 0.8rem;
    color: var(--color-muted);
    line-height: 1.65;
    margin: 0;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.related-card__date {
    font-size: 0.7rem;
    color: var(--color-muted);
    font-family: var(--font-mono);
    margin-top: 0.25rem;
}

/* ══════════════════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════════════════ */
@media (max-width: 640px) {
    .article-header { padding: 1.75rem 1.25rem; }
    .article-header__title { font-size: 1.65rem; }
    .article-header__meta { flex-direction: column; gap: 0.5rem; }
    .article-meta-divider { display: none; }
    .article-share { flex-direction: column; align-items: flex-start; }
}
</style>
@endsection
