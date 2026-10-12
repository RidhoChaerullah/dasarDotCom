<x-layouts.app title="Portal Laboratorium">
    <div class="lab-welcome">
        <section class="welcome-intro" aria-labelledby="welcome-title">
            <div class="welcome-copy">
                <p class="welcome-eyebrow">PORTAL LABORATORIUM KOMPUTER</p>
                <h1 id="welcome-title">Belajar dimulai<br>dari <span>dasar.</span></h1>
                <p class="welcome-description">
                    Materi praktikum, pengumpulan tugas, dan kegiatan laboratorium.
                    Satu ruang untuk terus berkembang.
                </p>
                <div class="welcome-actions">
                    <a href="{{ route('materials.index') }}" class="welcome-button welcome-button-primary">Jelajahi
                        materi <span aria-hidden="true">↗</span></a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="welcome-button welcome-button-secondary">Buka
                            dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="welcome-button welcome-button-secondary">Masuk ke akun</a>
                    @endauth
                </div>
                <p class="welcome-note">Materi terbuka untuk semua. Masuk untuk mengumpulkan tugas.</p>
                <a class="welcome-explore" href="#kegiatan">Lihat kegiatan lab <span aria-hidden="true">↓</span></a>
            </div>
            <div class="welcome-visual">
                <div class="welcome-orbit" aria-hidden="true"></div>
                <img src="{{ asset('images/asisten.png') }}" alt="Tim asisten laboratorium komputer" width="2048"
                    height="1409" fetchpriority="high" decoding="async">
            </div>
        </section>

        <section id="kegiatan" class="welcome-events" aria-labelledby="welcome-events-title">
            <div class="welcome-events-heading">
                <div>
                    <p class="welcome-eyebrow">TERHUBUNG & BERKEMBANG</p>
                    <h2 id="welcome-events-title">Ada cerita baru di lab.</h2>
                    <p>Temukan webinar, pelatihan, dan kegiatan berikutnya.</p>
                </div>
                <span class="welcome-events-label">RUANG UNTUK IDE BARU</span>
            </div>

            @if ($banners->isNotEmpty())
                <div class="welcome-gallery" x-data="{
                    index: 0,
                    total: @js($banners->count()),
                    go(next, instant = false) {
                        const track = this.$refs.track;
                        const target = Math.max(0, Math.min(next, this.total - 1));
                        const slide = track.children[target];
                        if (!slide) return;
                        const left = slide.offsetLeft - (track.clientWidth - slide.offsetWidth) / 2;
                        track.scrollTo({ left, behavior: instant || matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
                    },
                    sync() {
                        const track = this.$refs.track;
                        const center = track.scrollLeft + track.clientWidth / 2;
                        let closest = 0;
                        let distance = Infinity;
                        Array.from(track.children).forEach((slide, i) => {
                            const delta = Math.abs(slide.offsetLeft + slide.offsetWidth / 2 - center);
                            if (delta < distance) { distance = delta; closest = i; }
                        });
                        this.index = closest;
                    }
                }"
                    x-on:resize.window.debounce.150ms="go(index, true)">
                    <div class="welcome-gallery-track" x-ref="track" tabindex="0"
                        aria-label="Galeri kegiatan, geser untuk melihat poster berikutnya"
                        x-on:scroll.debounce.100ms="sync()" x-on:keydown.right.self.prevent="go(index + 1)"
                        x-on:keydown.left.self.prevent="go(index - 1)">
                        @foreach ($banners as $banner)
                            <article class="welcome-slide" x-bind:class="{ 'is-active': index === {{ $loop->index }} }"
                                aria-label="{{ $loop->iteration }} dari {{ $banners->count() }}">
                                <a href="{{ route('welcome.banners.image', $banner) }}" target="_blank"
                                    rel="noopener noreferrer" class="welcome-poster"
                                    aria-label="Buka poster {{ $banner->title }} di tab baru">
                                    <img class="welcome-poster-backdrop" src="{{ route('welcome.banners.image', $banner) }}"
                                        alt="" aria-hidden="true" loading="lazy" decoding="async">
                                    <img class="welcome-poster-image" src="{{ route('welcome.banners.image', $banner) }}" alt="{{ $banner->title }}"
                                        loading="lazy" decoding="async">
                                </a>
                                <div class="welcome-slide-caption">
                                    <div>
                                        <p>KEGIATAN LAB ·
                                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                        <h3>{{ $banner->title }}</h3>
                                    </div>
                                    @if ($banner->link_url && preg_match('/\Ahttps?:\/\//i', $banner->link_url))
                                        <a href="{{ $banner->link_url }}" target="_blank" rel="noopener noreferrer"
                                            class="welcome-button welcome-button-secondary">Info & pendaftaran <span
                                                aria-hidden="true">↗</span></a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if ($banners->count() > 1)
                        <div class="welcome-gallery-controls">
                            <p>Temukan kegiatan lainnya <span aria-hidden="true">↔</span></p>
                            <div>
                                <button type="button" x-on:click="go(index - 1)" x-bind:disabled="index === 0"
                                    aria-label="Poster sebelumnya">←</button>
                                <span aria-live="polite"><span x-text="index + 1">1</span> /
                                    {{ $banners->count() }}</span>
                                <button type="button" x-on:click="go(index + 1)" x-bind:disabled="index >= total - 1"
                                    aria-label="Poster berikutnya">→</button>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="welcome-events-empty">
                    <span class="welcome-eyebrow">SAMPAI JUMPA DI KEGIATAN BERIKUTNYA</span>
                    <h3>Ruang untuk hal-hal baru.</h3>
                    <p>Informasi kegiatan akan hadir di sini. Sambil menunggu, jelajahi materi praktikum yang tersedia.
                    </p>
                    <a href="{{ route('materials.index') }}" class="welcome-button welcome-button-secondary">Buka
                        materi →</a>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
