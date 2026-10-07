<script>
(() => {
    const root = document.documentElement;
    const apply = theme => { root.dataset.theme = theme === 'light' ? 'light' : 'dark'; };
    let saved;
    try { saved = localStorage.getItem('dasar-theme'); } catch (_) {}
    apply(saved || (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark'));
    document.addEventListener('click', event => {
        if (!event.target.closest('[data-theme-toggle]')) return;
        const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
        apply(next);
        try { localStorage.setItem('dasar-theme', next); } catch (_) {}
    });
    window.addEventListener('storage', event => {
        if (event.key === 'dasar-theme' && event.newValue) apply(event.newValue);
    });
})();
</script>
