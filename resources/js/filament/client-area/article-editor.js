/**
 * Article Editor — Client-Side Enhancements
 *
 * Responsibilities:
 *  1. Live word / read-time counter (reads from Trix editor)
 *  2. localStorage draft auto-save (Create page) / per-record draft (Edit page)
 *  3. Meta description character counter
 *  4. Save indicator micro-animation
 *
 * This script is vanilla JS — no framework dependencies.
 * It initialises safely on DOMContentLoaded and also listens for
 * Livewire page navigations (livewire:navigated).
 */

(function () {
    'use strict';

    /* ── Helpers ───────────────────────────────────────────────── */
    function debounce(fn, delay) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function countWords(html) {
        const text = html.replace(/<[^>]*>/g, ' ');
        const words = text.trim().split(/\s+/).filter(Boolean);
        return words.length;
    }

    function readTimeMinutes(wordCount) {
        return Math.max(1, Math.ceil(wordCount / 200));
    }

    function getInitials(name) {
        if (!name) return '?';
        return name
            .split(' ')
            .slice(0, 2)
            .map((n) => n[0])
            .join('')
            .toUpperCase();
    }

    /* ── Module: Word Count ─────────────────────────────────────── */
    function initWordCount() {
        const badge = document.getElementById('ae-word-count');
        if (!badge) return;

        function update(editor) {
            const wordCount = countWords(editor.innerHTML || '');
            const mins = readTimeMinutes(wordCount);
            badge.textContent = `${wordCount.toLocaleString()} kata · ~${mins} mnt baca`;
        }

        // Trix fires trix-change on every keystroke
        document.addEventListener('trix-change', function (e) {
            update(e.target);
        });

        // Initial render if editor already has content
        const trix = document.querySelector('trix-editor');
        if (trix) update(trix);
    }

    /* ── Module: Meta Description Counter ──────────────────────── */
    function initMetaCounter() {
        const counter = document.getElementById('ae-meta-count');
        if (!counter) return;
        const MAX = 160;

        function findMetaTextarea() {
            // Filament renders the textarea with wire:model — find by input name pattern
            return (
                document.querySelector('textarea[wire\\:model*="meta_description"]') ||
                document.querySelector('textarea[id*="meta_description"]')
            );
        }

        function update(textarea) {
            const len = (textarea.value || '').length;
            counter.textContent = `${len} / ${MAX}`;
            counter.parentElement.classList.remove('is-over-limit', 'is-near-limit');
            if (len > MAX) {
                counter.parentElement.classList.add('is-over-limit');
            } else if (len >= MAX * 0.9) {
                counter.parentElement.classList.add('is-near-limit');
            }
        }

        const ta = findMetaTextarea();
        if (ta) {
            update(ta);
            ta.addEventListener('input', () => update(ta));
        }

        // Filament may inject the textarea after Alpine init — observe DOM
        const observer = new MutationObserver(() => {
            const found = findMetaTextarea();
            if (found && !found._aeListened) {
                found._aeListened = true;
                update(found);
                found.addEventListener('input', () => update(found));
                observer.disconnect();
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    /* ── Module: Draft Auto-Save (localStorage) ─────────────────── */
    function initDraftAutoSave() {
        // Only on Create page (no record ID in URL)
        const isCreatePage = window.location.pathname.endsWith('/create');
        const isEditPage = /\/\d+\/edit$/.test(window.location.pathname);

        if (!isCreatePage && !isEditPage) return;

        // Build a storage key unique to the user & page mode
        const userId = document.querySelector('meta[name="user-id"]')?.content || 'anon';
        const recordMatch = window.location.pathname.match(/\/(\d+)\/edit$/);
        const storageKey = isEditPage
            ? `draft_article_edit_${recordMatch[1]}_${userId}`
            : `draft_article_create_${userId}`;

        const indicator = document.getElementById('ae-save-indicator');

        function showSaveIndicator() {
            if (!indicator) return;
            indicator.classList.add('is-visible');
            setTimeout(() => indicator.classList.remove('is-visible'), 2000);
        }

        // Listen for Livewire wire:model updates via the Livewire JS API
        // Livewire 3: $wire is available globally on the Livewire component
        function wireComponentReady() {
            const wire = window.Livewire?.find(
                document.querySelector('[wire\\:id]')?.getAttribute('wire:id')
            );
            if (!wire) return false;

            const debouncedSave = debounce((data) => {
                try {
                    localStorage.setItem(storageKey, JSON.stringify(data));
                    showSaveIndicator();
                } catch (e) {
                    // storage quota exceeded — silently ignore
                }
            }, 800);

            wire.$watch('data', (val) => debouncedSave(val));

            // Listen for successful submit (article-created event from afterCreate())
            wire.$on('article-created', () => {
                localStorage.removeItem(storageKey);
            });

            // On Create page: restore draft if fields are empty
            if (isCreatePage) {
                const saved = localStorage.getItem(storageKey);
                if (saved) {
                    try {
                        const parsed = JSON.parse(saved);
                        const currentData = wire.$get('data');
                        // Only restore if title is empty (fresh form)
                        const titleVal = currentData?.title;
                        const isEmpty =
                            !titleVal ||
                            (typeof titleVal === 'object' && !Object.values(titleVal).some(Boolean)) ||
                            titleVal === '';
                        if (isEmpty && parsed.title) {
                            wire.$set('data', { ...(currentData || {}), ...parsed });
                        }
                    } catch (e) { /* ignore */ }
                }
            }

            return true;
        }

        // Livewire may not be ready on first tick — retry a couple times
        let tries = 0;
        const interval = setInterval(() => {
            if (wireComponentReady() || ++tries > 20) {
                clearInterval(interval);
            }
        }, 250);
    }

    /* ── Module: Author Avatar Initials ─────────────────────────── */
    function initAuthorAvatar() {
        const avatars = document.querySelectorAll('.ae-author-avatar[data-name]');
        avatars.forEach((el) => {
            el.textContent = getInitials(el.dataset.name);
        });
    }

    /* ── Bootstrap ──────────────────────────────────────────────── */
    function boot() {
        initWordCount();
        initMetaCounter();
        initDraftAutoSave();
        initAuthorAvatar();
    }

    // Standard DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    // Livewire 3 SPA navigations
    document.addEventListener('livewire:navigated', boot);
})();
