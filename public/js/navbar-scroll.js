/* One navbar, two positions. No cloned links or duplicate menu IDs. */
(() => {
    if (window.dasarNavbarScrollInstalled) return;
    window.dasarNavbarScrollInstalled = true;
    let cleanup = () => {};

    function init() {
        cleanup();
        const anchor = document.querySelector('[data-navbar-anchor]');
        const header = anchor?.querySelector('.lab-navbar');
        if (!header) return;
        let frame = 0;
        let floating = false;

        function reserveSpace() {
            if (!floating) anchor.style.height = `${header.offsetHeight}px`;
        }

        function update() {
            frame = 0;
            reserveSpace();
            const bottom = anchor.getBoundingClientRect().bottom;
            // Small dead zone prevents flickering around the switch point.
            const next = floating ? bottom <= 0 : bottom < -16;
            if (next === floating) return;
            floating = next;
            header.classList.toggle('is-floating', floating);
            if (!floating) reserveSpace();
        }

        function schedule() {
            if (!frame) frame = requestAnimationFrame(update);
        }

        const observer = new ResizeObserver(schedule);
        observer.observe(header);
        window.addEventListener('scroll', schedule, { passive: true });
        window.addEventListener('resize', schedule, { passive: true });
        window.addEventListener('pageshow', schedule);
        update();
        cleanup = () => {
            cancelAnimationFrame(frame);
            observer.disconnect();
            window.removeEventListener('scroll', schedule);
            window.removeEventListener('resize', schedule);
            window.removeEventListener('pageshow', schedule);
            header.classList.remove('is-floating');
            anchor.style.removeProperty('height');
        };
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
    else init();
    document.addEventListener('livewire:navigated', init);
})();
