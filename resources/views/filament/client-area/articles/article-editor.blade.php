{{--
    Article Editor — Unified Create / Edit View
    Shared by CreateClientArticle and EditClientArticle.

    This view:
    1. Wraps Filament's native x-filament-panels::page (keeps header actions, breadcrumbs, etc.)
    2. Applies the `.article-editor-page` scope anchor for our scoped CSS
    3. Loads per-page assets (article-editor.css + article-editor.js) via @vite
    4. Renders {{ $this->form }} — the full Grid schema defined in ClientArticleResource::form()
    5. Injects our custom chrome: sticky writing header, author card, word-count badge,
       meta counter, and draft-save indicator

    NOTE: We do NOT use raw wire:model here. All data binding is handled by Filament's
          form schema (ClientArticleResource::form()). This file is purely layout chrome.
--}}

@php
    use Illuminate\Support\Str;
    $isCreate = $this instanceof \App\Filament\ClientArea\Resources\ClientArticleResource\Pages\CreateClientArticle;
    $record   = $isCreate ? null : $this->record;
    $status   = $record?->status ?? 'Draft';
    $badgeClass = match ($status) {
        'Published'     => 'is-published',
        'Pending Review'=> 'is-pending',
        default         => 'is-draft',
    };
    $authorName = auth()->user()?->name ?? 'Author';
    $authorInitials = collect(explode(' ', $authorName))->take(2)->map(fn($n) => Str::upper(Str::substr($n,0,1)))->implode('');
@endphp

<x-filament-panels::page>
    {{-- ── Load scoped assets via Vite ───────────────────────────────────── --}}
    @vite([
        'resources/css/filament/client-area/article-editor.css',
        'resources/js/filament/client-area/article-editor.js',
    ])

    {{-- ── User ID meta tag (used by article-editor.js for localStorage key) -- --}}
    <meta name="user-id" content="{{ auth()->id() }}">

    {{-- ── Scope anchor ──────────────────────────────────────────────────── --}}
    <div class="article-editor-page">

        {{-- ── Compact writing-mode header bar ─────────────────────────────── --}}
        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">

            {{-- Status badge (shows current article status) --}}
            <div class="flex items-center gap-3">
                <span class="article-status-badge {{ $badgeClass }}">
                    <span class="article-status-dot"></span>
                    {{ $status }}
                </span>

                {{-- Draft saved indicator (shown by article-editor.js) --}}
                <span class="draft-save-indicator" id="ae-save-indicator">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Draft tersimpan otomatis
                </span>
            </div>

            {{-- Word count badge (updated by article-editor.js) --}}
            <span class="word-count-badge" id="ae-word-count">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h8m-8 6h16" />
                </svg>
                0 kata · ~1 mnt baca
            </span>
        </div>

        {{-- ── Author identity card (above the main form) ─────────────────── --}}
        <div class="author-card mb-5">
            <div class="author-avatar ae-author-avatar" data-name="{{ $authorName }}">
                {{ $authorInitials }}
            </div>
            <div>
                <div class="author-info-name">{{ $authorName }}</div>
                <div class="author-info-role">Editor · Client Area</div>
            </div>
        </div>

        {{-- ── Main Filament Form ───────────────────────────────────────────── --}}
        {{-- $this->form renders the full Grid::make(3) schema from ClientArticleResource --}}
        {{ $this->form }}

        {{-- ── Meta description live counter ──────────────────────────────── --}}
        {{--
            Injected below the form; article-editor.js finds the SEO textarea via
            [wire:model*="meta_description"] and updates this badge.
        --}}
        <div class="meta-count-indicator mt-1" style="display:none" id="ae-meta-count-wrapper">
            <span id="ae-meta-count">0 / 160</span>
        </div>

        {{-- ── Form action buttons ──────────────────────────────────────────── --}}
        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </div>
</x-filament-panels::page>
