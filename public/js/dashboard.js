// Keep JavaScript out of HTML attributes so formatters cannot break operators.
window.dasarHomeGallery = function (total) {
    return {
        current: 0,
        total: Number(total),
        delay: 6000,
        timer: null,
        paused: false,
        hovering: false,
        focused: false,
        touching: false,
        touchX: 0,
        touchY: 0,
        init() {
            this.paused = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            this.start();
        },
        start() {
            clearInterval(this.timer);
            this.timer = null;
            if (this.total < 2) return;
            this.timer = setInterval(() => {
                if (this.paused || this.hovering || this.focused || this.touching || document.hidden) return;
                this.current = (this.current + 1) % this.total;
            }, this.delay);
        },
        select(index) {
            if (this.total < 1) return;
            this.current = (index + this.total) % this.total;
            this.start();
        },
        swipe(event) {
            if (!this.touching) return;
            const touch = event.changedTouches[0];
            if (touch) {
                const dx = touch.clientX - this.touchX;
                const dy = touch.clientY - this.touchY;
                if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy)) {
                    this.select(this.current + (dx < 0 ? 1 : -1));
                }
            }
            this.touching = false;
            this.start();
        },
        destroy() {
            clearInterval(this.timer);
            this.timer = null;
        }
    };
};
