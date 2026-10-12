/* Success-only full-document transition. No Livewire internal hooks. */
(() => {
    'use strict';
    if (window.DasarAuthTransition) return;

    const KEY = 'dasar.auth-transition.v1';
    const root = document.documentElement;
    const reducedMotion = () => matchMedia('(prefers-reduced-motion: reduce)').matches;
    let overlay, navigating = false, watchdog;

    const readMarker = () => {
        try {
            const value = JSON.parse(sessionStorage.getItem(KEY));
            sessionStorage.removeItem(KEY);
            return value && Date.now() - value.at < 30000 && value.url === location.href;
        } catch { return false; }
    };

    const reset = () => {
        clearTimeout(watchdog);
        overlay?.remove();
        overlay = null;
        navigating = false;
        root.classList.remove('auth-transition-active', 'auth-transition-arriving');
    };

    function createOverlay(covered = false, target = null) {
        overlay = document.createElement('div');
        overlay.className = 'auth-transition' + (covered ? ' is-covered' : '');
        overlay.setAttribute('role', 'status');
        overlay.setAttribute('aria-live', 'polite');
        overlay.innerHTML = '<div class="auth-transition-content"><div class="auth-transition-brand">dasar<span>.</span>com</div><p class="auth-transition-message">Menyiapkan halamanmu…</p></div>';
        // Compensate for the existing desktop body zoom without changing it.
        overlay.style.zoom = String(1 / (parseFloat(getComputedStyle(document.body).zoom) || 1));
        if (target) {
            const retry = document.createElement('a');
            retry.className = 'auth-transition-retry';
            retry.href = target;
            retry.textContent = 'Lanjut ke halaman tujuan';
            retry.hidden = true;
            overlay.firstElementChild.append(retry);
            watchdog = setTimeout(() => { retry.hidden = false; }, 8000);
        }
        document.body.append(overlay);
        root.classList.add('auth-transition-active');
        return overlay;
    }

    function duration() {
        if (reducedMotion()) return 0;
        const value = getComputedStyle(root).getPropertyValue('--auth-transition-duration').trim();
        return value.endsWith('ms') ? parseFloat(value) || 320 : (parseFloat(value) || .32) * 1000;
    }

    function go(value) {
        if (navigating) return;
        const target = new URL(value, location.href);
        // Cross-origin redirects and reduced motion use the ordinary redirect.
        if (target.origin !== location.origin || reducedMotion()) {
            location.assign(target.href);
            return;
        }
        navigating = true;
        try {
            const panel = createOverlay(false, target.href);
            panel.getBoundingClientRect();
            panel.classList.add('is-covered');
            setTimeout(() => {
                try { sessionStorage.setItem(KEY, JSON.stringify({ url: target.href, at: Date.now() })); } catch {}
                location.assign(target.href);
            }, duration());
        } catch {
            reset();
            location.assign(target.href);
        }
    }

    window.DasarAuthTransition = { go };
    window.addEventListener('pageshow', event => { if (event.persisted) reset(); });

    // Runs in <head> to prevent the destination flashing before the reveal.
    if (readMarker() && !reducedMotion()) {
        root.classList.add('auth-transition-arriving');
        watchdog = setTimeout(reset, 4000); // Never leave a hidden document behind.
        document.addEventListener('DOMContentLoaded', () => {
            try {
                const panel = createOverlay(true);
                root.classList.remove('auth-transition-arriving');
                panel.getBoundingClientRect();
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    panel.classList.add('is-leaving');
                    setTimeout(reset, duration() + 60);
                }));
            } catch { reset(); }
        }, { once: true });
    }
})();
