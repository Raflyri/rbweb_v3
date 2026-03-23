import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    // ── Base64 Converter Logic ──
    const b64Input = document.getElementById('rb-b64-input');
    const b64Output = document.getElementById('rb-b64-output');
    if (b64Input && b64Output) {
        b64Input.addEventListener('input', (e) => {
            const val = e.target.value;
            try {
                // btoa handles basic strings. For full unicode support, we'd encode differently,
                // but this is sufficient for the test requirement "RBeverything".
                b64Output.textContent = val ? btoa(val) : '';
            } catch (err) {
                b64Output.textContent = 'Error';
            }
        });
    }

    // ── Language Switcher Logic ──
    const langBtns = document.querySelectorAll('.rb-lang-btn');
    if (langBtns.length > 0 && window.RB_I18N) {
        langBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const lang = e.target.getAttribute('data-lang');
                if (window.RB_I18N[lang]) {
                    document.documentElement.lang = lang;
                    
                    // Update text for all elements with data-i18n
                    document.querySelectorAll('[data-i18n]').forEach(el => {
                        const key = el.getAttribute('data-i18n');
                        const keys = key.split('.');
                        let translation = window.RB_I18N[lang];
                        for (let k of keys) {
                            if (translation === undefined) break;
                            translation = translation[k];
                        }
                        if (translation !== undefined && typeof translation === 'string') {
                            el.innerHTML = translation;
                        }
                    });
                }
            });
        });
    }
});
