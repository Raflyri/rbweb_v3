@extends('layouts.public')

{{-- ════════════════════════════════════════════════════════════
     SEO — Blog Index
════════════════════════════════════════════════════════════ --}}
@section('meta_title',       'Blog')
@section('meta_description', 'Latest insights, tutorials, and tech deep-dives from RBeverything.')
@section('og_type',          'website')
@section('og_title',         'Blog — RBeverything')
@section('og_description',   'Latest insights, tutorials, and tech deep-dives from RBeverything.')
@section('nav_blog_active',  'style="color:var(--color-text);"')

{{-- ════════════════════════════════════════════════════════════
     CONTENT
════════════════════════════════════════════════════════════ --}}
@section('content')

    {{-- ════════════════════════════════════════════════════════
         BLOG HERO HEADER
    ════════════════════════════════════════════════════════ --}}
    <section class="blog-hero" aria-label="Blog Header">
        <div class="rb-section" style="padding-bottom:2.5rem;">

            {{-- Breadcrumb --}}
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="/" class="breadcrumb__link">Home</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                <span class="breadcrumb__current">Blog</span>
            </nav>

            {{-- Title block --}}
            <div class="blog-hero__inner">
                <div class="blog-hero__text">
                    <span class="rb-section-label">Latest Insights</span>
                    <h1 class="rb-section-title" style="margin-bottom:0.75rem;">Blog</h1>
                    <p class="blog-hero__subtitle">
                        Deep-dives into technology, tutorials, and insights from our engineering team.
                    </p>
                </div>

                {{-- Search Bar --}}
                <form action="{{ route('blog.index') }}" method="GET"
                      id="blog-search-form" class="blog-search" role="search" aria-label="Search articles">
                    <div class="blog-search__field">
                        <svg class="blog-search__icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                        </svg>
                        <input type="text" name="search" id="blog-search-input"
                               value="{{ $search ?? '' }}"
                               placeholder="{{ __('Search articles…') }}"
                               autocomplete="off"
                               class="blog-search__input">
                        @if($search)
                            <a href="{{ route('blog.index') }}" class="blog-search__clear" aria-label="Clear search">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="rb-btn-primary blog-search__btn">
                        {{ __('Search') }}
                    </button>
                </form>
            </div>

            {{-- Active search notice --}}
            @if($search)
                <div class="blog-search-badge" role="status">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    <span>
                        {{ $articles->total() }} {{ Str::plural('result', $articles->total()) }} for
                        <strong>"{{ $search }}"</strong>
                    </span>
                </div>
            @endif
        </div>
    </section>

    <div class="rb-divider"></div>

    {{-- ════════════════════════════════════════════════════════
         ARTICLES GRID
    ════════════════════════════════════════════════════════ --}}
    <section aria-label="{{ __('Articles') }}" class="blog-articles">
        <div class="rb-section" style="padding-top:3.5rem;padding-bottom:5rem;">

            @if($articles->count() > 0)

                <div class="article-grid">
                    @foreach($articles as $article)
                        @php
                            /* ── Translatable fields ──────────────────────────── */
                            $title   = $article->getTranslation('title',   $locale, true);
                            $excerpt = $article->getTranslation('excerpt', $locale, false);

                            // Fallback: strip HTML from content if no excerpt set
                            if (empty(trim($excerpt))) {
                                $rawContent = $article->getTranslation('content', $locale, true);
                                $excerpt    = Str::limit(strip_tags($rawContent), 200);
                            }

                            /* ── Read-time estimate ───────────────────────────── */
                            $rawContent  = $article->getTranslation('content', $locale, true);
                            $wordCount   = str_word_count(strip_tags($rawContent));
                            $readMinutes = max(1, (int) ceil($wordCount / 200));

                            /* ── Author ───────────────────────────────────────── */
                            $authorName = $article->user?->name ?? 'RBeverything';

                            /* ── First tag ────────────────────────────────────── */
                            $firstTag = $article->tags->first();
                        @endphp

                        <article class="article-card" id="article-card-{{ $article->id }}">
                            {{-- ── Cover Image ──────────────────────────────── --}}
                            <a href="{{ route('blog.show', $article->getTranslation('slug', $locale, true)) }}"
                               class="article-card__cover-link" aria-label="{{ $title }}" tabindex="-1">
                                @if($article->thumbnail)
                                    <div class="article-card__cover">
                                        <img src="{{ asset('storage/' . $article->thumbnail) }}"
                                             alt="{{ $title }}"
                                             class="article-card__img"
                                             loading="lazy">
                                        <div class="article-card__cover-overlay" aria-hidden="true"></div>
                                    </div>
                                @else
                                    <div class="article-card__cover article-card__cover--placeholder">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                             stroke="rgba(220,38,38,0.25)" stroke-width="1"
                                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                                            <circle cx="8.5" cy="8.5" r="1.5"/>
                                            <polyline points="21 15 16 10 5 21"/>
                                        </svg>
                                    </div>
                                @endif
                            </a>

                            {{-- ── Card Body ────────────────────────────────── --}}
                            <div class="article-card__body">

                                {{-- Tags row --}}
                                <div class="article-card__tags" aria-label="Tags">
                                    @if($firstTag)
                                        <span class="article-tag">
                                            {{ $firstTag->getTranslation('name', $locale, false) ?: $firstTag->name }}
                                        </span>
                                        @if($article->tags->count() > 1)
                                            <span class="article-tag article-tag--more">
                                                +{{ $article->tags->count() - 1 }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="article-tag">Article</span>
                                    @endif
                                </div>

                                {{-- Title --}}
                                <h2 class="article-card__title">
                                    <a href="{{ route('blog.show', $article->getTranslation('slug', $locale, true)) }}"
                                       class="article-card__title-link">
                                        {{ $title }}
                                    </a>
                                </h2>

                                {{-- Excerpt (clamped to 2 lines via CSS) --}}
                                <p class="article-card__excerpt">{{ $excerpt }}</p>

                                {{-- ── Meta Footer ─────────────────────────── --}}
                                <div class="article-card__meta">
                                    {{-- Author avatar + name --}}
                                    <div class="article-card__author">
                                        <div class="article-card__avatar" aria-hidden="true">
                                            {{ strtoupper(mb_substr($authorName, 0, 1)) }}
                                        </div>
                                        <span class="article-card__author-name">{{ $authorName }}</span>
                                    </div>

                                    {{-- Read time + date --}}
                                    <div class="article-card__info">
                                        <span class="article-card__read-time" title="{{ __('Estimated reading time') }}">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                            {{ $readMinutes }} min read
                                        </span>
                                        @if($article->published_at)
                                            <span class="article-card__dot" aria-hidden="true">·</span>
                                            <time class="article-card__date"
                                                  datetime="{{ $article->published_at->toIso8601String() }}">
                                                {{ $article->published_at->format('M d, Y') }}
                                            </time>
                                        @endif
                                    </div>
                                </div>

                                {{-- Read More CTA --}}
                                <a href="{{ route('blog.show', $article->getTranslation('slug', $locale, true)) }}"
                                   class="article-card__cta" aria-label="Read {{ $title }}">
                                    Read Article
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M5 12h14M12 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- ── Pagination ───────────────────────────────────── --}}
                @if($articles->hasPages())
                    <div class="blog-pagination" id="blog-pagination" aria-label="Pagination">
                        {{ $articles->links() }}
                    </div>
                @endif

            @else
                {{-- ════════════════════════════════════════════════
                     EMPTY STATE
                ════════════════════════════════════════════════ --}}
                <div class="blog-empty" id="blog-empty-state" role="status">
                    @if($search)
                        <div class="blog-empty__icon-wrap blog-empty__icon-wrap--search">
                            <svg class="blog-empty__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m13.5 8.5-5 5"/><path d="m8.5 8.5 5 5"/>
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                        </div>
                        <h3 class="blog-empty__title">{{ __('No articles found') }}</h3>
                        <p class="blog-empty__desc">
                            {{ __('We couldn\'t find any articles matching ":search". Try different keywords.', ['search' => $search]) }}
                        </p>
                        <a href="{{ route('blog.index') }}" class="blog-empty__cta">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                            </svg>
                            {{ __('Clear search') }}
                        </a>
                    @else
                        <div class="blog-empty__icon-wrap">
                            <svg class="blog-empty__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/>
                            </svg>
                        </div>
                        <h3 class="blog-empty__title">{{ __('No articles yet') }}</h3>
                        <p class="blog-empty__desc">
                            {{ __('We\'re actively preparing new tech insights and tutorials. Please check back soon!') }}
                        </p>
                    @endif
                </div>
            @endif

        </div>
    </section>

@endsection

{{-- ════════════════════════════════════════════════════════════
     PAGE-SPECIFIC STYLES
════════════════════════════════════════════════════════════ --}}
@section('styles')
<style>
/* ══════════════════════════════════════════════════════
   BLOG HERO
══════════════════════════════════════════════════════ */
.blog-hero {}

.blog-hero__inner {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 2rem;
    margin-top: 2rem;
}

.blog-hero__text {
    flex: 1;
    min-width: 0;
}

.blog-hero__subtitle {
    font-size: 1.05rem;
    color: var(--color-muted);
    font-weight: 300;
    max-width: 34rem;
    line-height: 1.75;
    margin: 0;
}

/* ── Breadcrumb ─────────────────────────────────────── */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: var(--color-muted);
    margin-bottom: 2rem;
}
.breadcrumb svg { opacity: 0.4; flex-shrink: 0; }
.breadcrumb__link {
    color: var(--color-muted);
    text-decoration: none;
    transition: color 0.25s;
}
.breadcrumb__link:hover { color: #DC2626; }
.breadcrumb__current { color: var(--color-text); }

/* ── Search ─────────────────────────────────────────── */
.blog-search {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    width: 100%;
    max-width: 26rem;
    flex-shrink: 0;
}

.blog-search__field {
    position: relative;
    flex: 1;
    min-width: 0;
}

.blog-search__icon {
    position: absolute;
    left: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: var(--color-muted);
    flex-shrink: 0;
}

.blog-search__input {
    width: 100%;
    padding: 0.7rem 2.6rem 0.7rem 2.75rem;
    border-radius: 9999px;
    border: 1px solid rgba(255,255,255,0.08);
    background: rgba(255,255,255,0.04);
    color: var(--color-text);
    font-size: 0.875rem;
    font-family: inherit;
    outline: none;
    transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
}
.blog-search__input::placeholder { color: var(--color-muted); }
.blog-search__input:focus {
    border-color: rgba(220,38,38,0.35);
    box-shadow: 0 0 0 3px rgba(220,38,38,0.09);
    background: rgba(255,255,255,0.06);
}

.blog-search__clear {
    position: absolute;
    right: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-muted);
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s;
}
.blog-search__clear:hover { color: #DC2626; }

.blog-search__btn {
    padding: 0.7rem 1.35rem;
    font-size: 0.82rem;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ── Search result badge ────────────────────────────── */
.blog-search-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1.75rem;
    padding: 0.45rem 1rem;
    border-radius: 9999px;
    background: rgba(220,38,38,0.07);
    border: 1px solid rgba(220,38,38,0.2);
    font-size: 0.82rem;
    color: var(--color-muted);
}
.blog-search-badge svg { color: #DC2626; flex-shrink: 0; }
.blog-search-badge strong { color: var(--color-text); font-weight: 600; }

/* ══════════════════════════════════════════════════════
   ARTICLE GRID
══════════════════════════════════════════════════════ */
.article-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 2rem;
}
@media (min-width: 640px)  { .article-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .article-grid { grid-template-columns: repeat(3, 1fr); } }

/* ══════════════════════════════════════════════════════
   ARTICLE CARD
══════════════════════════════════════════════════════ */
.article-card {
    display: flex;
    flex-direction: column;
    background: rgba(255,255,255,0.025);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 1.25rem;
    overflow: hidden;
    transition:
        border-color 0.35s cubic-bezier(0.4,0,0.2,1),
        box-shadow   0.35s cubic-bezier(0.4,0,0.2,1),
        transform    0.35s cubic-bezier(0.4,0,0.2,1);
    position: relative;
}

.article-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 0% 0%, rgba(220,38,38,0.05), transparent 65%);
    opacity: 0;
    transition: opacity 0.4s;
    pointer-events: none;
    z-index: 0;
}

.article-card:hover {
    border-color: rgba(220,38,38,0.28);
    box-shadow: 0 0 0 1px rgba(220,38,38,0.12), 0 20px 50px -12px rgba(0,0,0,0.55);
    transform: translateY(-5px);
}
.article-card:hover::before { opacity: 1; }

/* ── Cover image ────────────────────────────────────── */
.article-card__cover-link {
    display: block;
    text-decoration: none;
    position: relative;
    z-index: 1;
}

.article-card__cover {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(220,38,38,0.08), rgba(185,28,28,0.03));
}

.article-card__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.6s cubic-bezier(0.4,0,0.2,1);
}
.article-card:hover .article-card__img {
    transform: scale(1.06);
}

.article-card__cover-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(3,5,8,0.5) 0%, transparent 50%);
    opacity: 0;
    transition: opacity 0.35s;
}
.article-card:hover .article-card__cover-overlay { opacity: 1; }

.article-card__cover--placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── Body ───────────────────────────────────────────── */
.article-card__body {
    display: flex;
    flex-direction: column;
    flex: 1;
    padding: 1.5rem;
    gap: 0.75rem;
    position: relative;
    z-index: 1;
}

/* ── Tags ───────────────────────────────────────────── */
.article-card__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
}

.article-tag {
    display: inline-block;
    padding: 0.2rem 0.65rem;
    border-radius: 9999px;
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    background: rgba(220,38,38,0.1);
    color: #DC2626;
    border: 1px solid rgba(220,38,38,0.22);
    line-height: 1.6;
    white-space: nowrap;
}

.article-tag--more {
    background: rgba(255,255,255,0.04);
    color: var(--color-muted);
    border-color: rgba(255,255,255,0.08);
}

/* ── Title ──────────────────────────────────────────── */
.article-card__title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    letter-spacing: -0.018em;
    line-height: 1.38;
}

.article-card__title-link {
    color: var(--color-text);
    text-decoration: none;
    transition: color 0.25s;
    /* Clamp to 3 lines */
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.article-card__title-link:hover { color: #DC2626; }

/* ── Excerpt ────────────────────────────────────────── */
.article-card__excerpt {
    margin: 0;
    font-size: 0.855rem;
    color: var(--color-muted);
    line-height: 1.7;
    flex: 1;
    /* Hard 2-line clamp */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ── Meta footer ────────────────────────────────────── */
.article-card__meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding-top: 0.875rem;
    border-top: 1px solid rgba(255,255,255,0.055);
    margin-top: auto;
}

.article-card__author {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 0;
}

.article-card__avatar {
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(220,38,38,0.25), rgba(185,28,28,0.15));
    border: 1px solid rgba(220,38,38,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    font-weight: 800;
    color: #DC2626;
    flex-shrink: 0;
    letter-spacing: 0;
}

.article-card__author-name {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--color-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 8rem;
}

.article-card__info {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-shrink: 0;
}

.article-card__read-time {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.72rem;
    color: var(--color-muted);
    font-family: var(--font-mono);
    white-space: nowrap;
}

.article-card__dot {
    font-size: 0.7rem;
    color: var(--color-muted);
    opacity: 0.5;
}

.article-card__date {
    font-size: 0.72rem;
    color: var(--color-muted);
    font-family: var(--font-mono);
    white-space: nowrap;
}

/* ── CTA ────────────────────────────────────────────── */
.article-card__cta {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    font-weight: 700;
    color: #DC2626;
    text-decoration: none;
    letter-spacing: 0.01em;
    transition: gap 0.25s, color 0.25s;
    margin-top: 0.25rem;
    align-self: flex-start;
}
.article-card__cta svg {
    transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
}
.article-card:hover .article-card__cta { color: #EF4444; }
.article-card:hover .article-card__cta svg { transform: translateX(4px); }

/* ══════════════════════════════════════════════════════
   PAGINATION
══════════════════════════════════════════════════════ */
.blog-pagination {
    margin-top: 4rem;
    display: flex;
    justify-content: center;
}
.blog-pagination nav[role="navigation"] {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
}
.blog-pagination nav[role="navigation"] .flex-1 { display: none; }
.blog-pagination nav[role="navigation"] .hidden { display: flex !important; gap: 0.3rem; }
.blog-pagination nav[role="navigation"] span[aria-current="page"] span,
.blog-pagination nav[role="navigation"] a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.35rem;
    height: 2.35rem;
    padding: 0 0.5rem;
    border-radius: 0.6rem;
    font-size: 0.82rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.25s ease;
    border: 1px solid rgba(255,255,255,0.07);
    font-family: var(--font-mono);
}
.blog-pagination nav[role="navigation"] span[aria-current="page"] span {
    background: rgba(220,38,38,0.14);
    border-color: rgba(220,38,38,0.35);
    color: #DC2626;
    font-weight: 700;
}
.blog-pagination nav[role="navigation"] a {
    color: var(--color-muted);
    background: rgba(255,255,255,0.02);
}
.blog-pagination nav[role="navigation"] a:hover {
    background: rgba(220,38,38,0.07);
    border-color: rgba(220,38,38,0.22);
    color: var(--color-text);
}
.blog-pagination nav[role="navigation"] span.cursor-default { display: none; }
.blog-pagination nav[role="navigation"] p {
    font-size: 0.78rem;
    color: var(--color-muted);
}

/* ══════════════════════════════════════════════════════
   EMPTY STATE
══════════════════════════════════════════════════════ */
.blog-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 6rem 1.5rem;
    text-align: center;
}

.blog-empty__icon-wrap {
    width: 5rem;
    height: 5rem;
    border-radius: 50%;
    background: rgba(220,38,38,0.07);
    border: 1px solid rgba(220,38,38,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.75rem;
    box-shadow: 0 0 40px rgba(220,38,38,0.08);
}
.blog-empty__icon {
    width: 2.25rem;
    height: 2.25rem;
    color: rgba(220,38,38,0.6);
}

.blog-empty__title {
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--color-text);
    margin: 0 0 0.75rem;
    font-family: var(--font-sans);
}

.blog-empty__desc {
    font-size: 0.95rem;
    color: var(--color-muted);
    max-width: 28rem;
    line-height: 1.7;
    margin: 0 0 2rem;
}

.blog-empty__cta {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #DC2626;
    text-decoration: none;
    padding: 0.6rem 1.5rem;
    border-radius: 9999px;
    border: 1px solid rgba(220,38,38,0.28);
    transition: background 0.25s, box-shadow 0.25s;
}
.blog-empty__cta:hover {
    background: rgba(220,38,38,0.07);
    box-shadow: 0 0 20px rgba(220,38,38,0.1);
}

/* ══════════════════════════════════════════════════════
   RESPONSIVE TWEAKS
══════════════════════════════════════════════════════ */
@media (max-width: 640px) {
    .blog-hero__inner { flex-direction: column; align-items: flex-start; }
    .blog-search { max-width: 100%; }
    .article-card__meta { flex-direction: column; align-items: flex-start; }
}
</style>
@endsection
