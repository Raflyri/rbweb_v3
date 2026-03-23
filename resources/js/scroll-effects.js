/**
 * RBeverything — scroll-effects.js v3.1
 * Features: Lenis smooth scroll, i18n engine, typewriter, reveal,
 *           navbar, carousel drag, hero parallax, Base64 card,
 *           language switcher, mobile hamburger.
 */

/* ════════════════════════════════════════════════════════
   1. LENIS SMOOTH SCROLLING
════════════════════════════════════════════════════════ */
let lenis;
if (typeof Lenis !== 'undefined') {
    lenis = new Lenis({
        duration: 1.3,
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
        orientation: 'vertical',
        smoothWheel: true,
    });
    function raf(time) { lenis.raf(time); requestAnimationFrame(raf); }
    requestAnimationFrame(raf);
}

/* ════════════════════════════════════════════════════════
   2. i18n ENGINE
════════════════════════════════════════════════════════ */
const SUPPORTED_LOCALES = ['en', 'id', 'ms', 'ja'];
const BROWSER_MAP = {
    'id': 'id', 'in': 'id',            // Indonesian
    'ms': 'ms', 'my': 'ms',            // Malay
    'ja': 'ja', 'jp': 'ja',            // Japanese
};
let currentLocale = 'en';

/**
 * Deep-get a value from a nested object using a dot-key like "hero.subtitle".
 */
function i18nGet(obj, key) {
    return key.split('.').reduce((o, k) => (o && o[k] !== undefined ? o[k] : null), obj);
}

/**
 * Apply translations to all [data-i18n] elements in the DOM.
 * Supports: textContent, placeholder, aria-label.
 */
function applyTranslations(locale) {
    const dict = window.RB_I18N && window.RB_I18N[locale];
    if (!dict) return;
    currentLocale = locale;

    document.querySelectorAll('[data-i18n]').forEach((el) => {
        const key = el.dataset.i18n;
        const val = i18nGet(dict, key);
        if (val !== null && typeof val === 'string') {
            if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                el.placeholder = val;
            } else if (el.hasAttribute('aria-label')) {
                el.setAttribute('aria-label', val);
            } else {
                el.textContent = val;
            }
        }
    });

    // Update typewriter phrases from i18n
    const phrases = i18nGet(dict, 'hero.phrases');
    if (phrases && Array.isArray(phrases)) {
        window._twPhrases = phrases;
    }

    // Mark active lang button
    document.querySelectorAll('.rb-lang-btn').forEach((btn) => {
        btn.classList.toggle('active', btn.dataset.lang === locale);
    });

    // Update html lang attribute
    document.documentElement.lang = locale;
}

/**
 * Detect the best locale for this visitor.
 * Priority: localStorage → navigator.language → 'en'
 */
function detectLocale() {
    const stored = localStorage.getItem('rb_locale');
    if (stored && SUPPORTED_LOCALES.includes(stored)) return stored;

    const browser = (navigator.language || 'en').slice(0, 2).toLowerCase();
    return BROWSER_MAP[browser] || 'en';
}

/**
 * Switch language, persist, update DOM, sync server session.
 */
function switchLang(locale) {
    if (!SUPPORTED_LOCALES.includes(locale)) return;
    localStorage.setItem('rb_locale', locale);
    applyTranslations(locale);

    // Silently sync server session (fire-and-forget)
    fetch(`/lang/${locale}`, { method: 'GET', credentials: 'same-origin' }).catch(() => {});
}

// Expose globally so inline onclick can call it
window.RBswitchLang = switchLang;

/* ════════════════════════════════════════════════════════
   3. TYPEWRITER
════════════════════════════════════════════════════════ */
function initTypewriter() {
    const el = document.getElementById('typewriter-target');
    if (!el) return;

    // Use the window._twPhrases set by i18n, or fallback
    const getPhrases = () => window._twPhrases || [
        'IT Consulting', 'Web Development', 'AI Implementation',
        'System Architecture', 'Digital Excellence',
    ];

    let phraseIdx = 0, charIdx = 0, isDeleting = false;
    const SPEED_TYPE = 70, SPEED_DEL = 40, PAUSE_FULL = 2200, PAUSE_EMPTY = 380;

    function tick() {
        const phrases = getPhrases();
        const current = phrases[phraseIdx % phrases.length];

        if (isDeleting) {
            charIdx--;
        } else {
            charIdx++;
        }

        el.textContent = current.slice(0, Math.max(0, charIdx));

        if (!isDeleting && charIdx === current.length) {
            isDeleting = true;
            setTimeout(tick, PAUSE_FULL);
        } else if (isDeleting && charIdx === 0) {
            isDeleting = false;
            phraseIdx++;
            setTimeout(tick, PAUSE_EMPTY);
        } else {
            setTimeout(tick, isDeleting ? SPEED_DEL : SPEED_TYPE);
        }
    }

    setTimeout(tick, 900);
}

/* ════════════════════════════════════════════════════════
   4. NAVBAR SCROLL BEHAVIOUR
════════════════════════════════════════════════════════ */
function initNavbar() {
    const nav = document.getElementById('rb-nav');
    if (!nav) return;
    const handler = () => nav.classList.toggle('scrolled', window.scrollY > 60);
    window.addEventListener('scroll', handler, { passive: true });
    handler();
}

/* ════════════════════════════════════════════════════════
   5. INTERSECTION OBSERVER — REVEAL
════════════════════════════════════════════════════════ */
function initReveal() {
    const els = document.querySelectorAll('[data-reveal]');
    if (!els.length) return;
    const obs = new IntersectionObserver(
        (entries) => entries.forEach((e) => {
            if (e.isIntersecting) { e.target.classList.add('is-visible'); obs.unobserve(e.target); }
        }),
        { threshold: 0.1, rootMargin: '0px 0px -48px 0px' }
    );
    els.forEach((el) => obs.observe(el));
}

/* ════════════════════════════════════════════════════════
   6. HERO PARALLAX
════════════════════════════════════════════════════════ */
function initHeroParallax() {
    const hero = document.getElementById('rb-hero-content');
    if (!hero) return;
    window.addEventListener('scroll', () => {
        const sy = window.scrollY;
        if (sy < window.innerHeight) {
            hero.style.transform = `translateY(${sy * 0.22}px)`;
            hero.style.opacity   = Math.max(0, 1 - sy * 0.0018).toString();
        }
    }, { passive: true });
}

/* ════════════════════════════════════════════════════════
   7. MOBILE HAMBURGER
════════════════════════════════════════════════════════ */
function initHamburger() {
    const btn  = document.getElementById('rb-hamburger');
    const menu = document.getElementById('rb-mobile-menu');
    if (!btn || !menu) return;

    const toggle = (open) => {
        btn.classList.toggle('open', open);
        menu.classList.toggle('open', open);
        document.body.style.overflow = open ? 'hidden' : '';
        if (lenis) { open ? lenis.stop() : lenis.start(); }
    };

    btn.addEventListener('click', () => toggle(!menu.classList.contains('open')));
    menu.querySelectorAll('a, button').forEach((el) => {
        el.addEventListener('click', () => toggle(false));
    });
}

/* ════════════════════════════════════════════════════════
   8. BLOG CAROUSEL — DRAG TO SCROLL
════════════════════════════════════════════════════════ */
function initCarousel() {
    const track = document.getElementById('rb-carousel-track');
    if (!track) return;

    let down = false, startX = 0, scrollLeft = 0;
    const end = () => { down = false; track.classList.remove('dragging'); };

    track.addEventListener('mousedown',  (e) => {
        down = true; track.classList.add('dragging');
        startX = e.pageX - track.offsetLeft; scrollLeft = track.scrollLeft;
    });
    track.addEventListener('mouseleave', end);
    track.addEventListener('mouseup',    end);
    track.addEventListener('mousemove',  (e) => {
        if (!down) return; e.preventDefault();
        track.scrollLeft = scrollLeft - (e.pageX - track.offsetLeft - startX) * 1.8;
    });

    document.getElementById('rb-carousel-prev')
        ?.addEventListener('click', () => track.scrollBy({ left: -380, behavior: 'smooth' }));
    document.getElementById('rb-carousel-next')
        ?.addEventListener('click', () => track.scrollBy({ left: 380, behavior: 'smooth' }));
}

/* ════════════════════════════════════════════════════════
   9. BASE64 INTERACTIVE CARD
════════════════════════════════════════════════════════ */
function initBase64Card() {
    const input  = document.getElementById('rb-b64-input');
    const output = document.getElementById('rb-b64-output');
    const copyBtn = document.getElementById('rb-b64-copy');
    if (!input || !output) return;

    const encode = () => {
        try {
            const raw = input.value;
            output.textContent = raw ? btoa(unescape(encodeURIComponent(raw))) : '';
        } catch {
            output.textContent = '[invalid UTF-8]';
        }
    };

    input.addEventListener('input', encode);

    if (copyBtn) {
        copyBtn.addEventListener('click', () => {
            const text = output.textContent;
            if (!text) return;
            navigator.clipboard.writeText(text).then(() => {
                const orig = copyBtn.textContent;
                copyBtn.textContent = 'Copied!';
                setTimeout(() => { copyBtn.textContent = orig; }, 1800);
            });
        });
    }
}

/* ════════════════════════════════════════════════════════
   10. LANGUAGE SWITCHER BUTTONS
════════════════════════════════════════════════════════ */
function initLangSwitcher() {
    document.querySelectorAll('.rb-lang-btn').forEach((btn) => {
        btn.addEventListener('click', () => switchLang(btn.dataset.lang));
    });
}

/* ════════════════════════════════════════════════════════
   INIT — DOMContentLoaded
════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    // i18n — detect + apply immediately
    const locale = detectLocale();
    applyTranslations(locale);

    initTypewriter();
    initNavbar();
    initReveal();
    initHeroParallax();
    initHamburger();
    initCarousel();
    initBase64Card();
    initLangSwitcher();
});
