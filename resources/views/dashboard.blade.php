<x-layouts.app title="Dashboard">
    <script src="{{ asset('js/dashboard.js') }}"></script>

    @php
        $screenTaskUrl = trim((string) config('services.screentask.url', ''));
        $screenTaskConfigured =
            filter_var($screenTaskUrl, FILTER_VALIDATE_URL) !== false &&
            in_array(strtolower((string) parse_url($screenTaskUrl, PHP_URL_SCHEME)), ['http', 'https'], true);
    @endphp

    <div class="lab-dashboard">
        <header class="dash-welcome">
            <div class="dash-welcome-copy">
                <p class="dash-eyebrow"><span class="dash-status-dot" aria-hidden="true"></span>RUANG BELAJAR BERSAMA</p>
                <h1>Nyahaloo, {{ auth()->user()->name }}<span class="dash-accent">.</span></h1>
                <p class="dash-muted">Satu tempat untuk belajar, berkarya, dan berkembang.</p>
            </div>
            <div class="dash-date">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="16" rx="2" />
                    <path d="M16 3v4M8 3v4M3 11h18m-12 5h2m4 0h2" />
                </svg>
                <div>
                    <strong>{{ $today->locale('id')->translatedFormat('l, d F Y') }}</strong>
                    <span>{{ ucfirst(auth()->user()->role) }} · {{ auth()->user()->nim ?? 'Pengelola lab' }}</span>
                </div>
            </div>
        </header>

        <nav class="dash-shortcuts" aria-label="Akses cepat">
            <a href="{{ route('materials.index') }}" class="dash-shortcut is-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 7v14M3 4h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5v15h-5a4 4 0 0 0-4 2 4 4 0 0 0-4-2H3Z" />
                </svg> <span>Jelajahi materi</span> <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M7 17 17 7M7 7h10v10" />
                </svg>
            </a>
            <a href="{{ route('assignments.index') }}" class="dash-shortcut">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <rect x="5" y="4" width="14" height="17" rx="2" />
                    <rect x="9" y="2" width="6" height="4" rx="1" />
                    <path d="m9 13 2 2 4-4" />
                </svg> <span>Pengumpulan tugas</span> <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M7 17 17 7M7 7h10v10" />
                </svg>
            </a>
            <a href="{{ route('profile.index') }}" class="dash-shortcut">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M4 21v-2a8 8 0 0 1 16 0v2" />
                </svg> <span>Profil saya</span> <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M7 17 17 7M7 7h10v10" />
                </svg>
            </a>
        </nav>

        <div class="dash-columns">
            <section class="dash-events" aria-labelledby="dash-events-title">
                <div class="dash-section-heading">
                    <div>
                        <p class="dash-eyebrow">DI BALIK PRAKTIKUM</p>
                        <h2 id="dash-events-title">Ada cerita baru di lab.</h2>
                    </div>
                    @can('manage-lab')
                        <a href="{{ route('banners.index') }}" class="dash-manage" aria-label="Kelola banner"><svg
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m15 5 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15Z" />
                            </svg> <span>Kelola</span></a>
                    @endcan
                </div>

                @if ($banners->isNotEmpty())
                    <div class="dash-carousel" role="region" aria-roledescription="carousel"
                        aria-label="Poster kegiatan" x-data="dasarHomeGallery({{ $banners->count() }})" x-on:mouseenter="hovering = true"
                        x-on:mouseleave="hovering = false; start()" x-on:focusin="focused = true"
                        x-on:focusout="focused = $el.contains($event.relatedTarget); start()"
                        x-on:keydown.left.prevent="select(current - 1)"
                        x-on:keydown.right.prevent="select(current + 1)">
                        <div class="gallery-window"
                            x-on:touchstart.passive="touching = true; touchX = $event.touches[0].clientX; touchY = $event.touches[0].clientY"
                            x-on:touchend.passive="swipe($event)"
                            x-on:touchcancel.passive="touching = false; start()">
                            <div class="gallery-track"
                                x-bind:style="{ transform: 'translateX(-' + (current * 100) + '%)' }">
                                @foreach ($banners as $banner)
                                    <article class="gallery-slide" role="group" aria-roledescription="slide"
                                        aria-label="{{ $loop->iteration }} dari {{ $banners->count() }}"
                                        x-bind:aria-hidden="current !== {{ $loop->index }}"
                                        x-bind:inert="current !== {{ $loop->index }}">
                                        <a href="{{ route('banners.image', $banner) }}" target="_blank"
                                            rel="noopener noreferrer" class="dash-poster-stage"
                                            aria-label="Buka poster {{ $banner->title }} di tab baru">
                                            <img class="dash-poster" src="{{ route('banners.image', $banner) }}"
                                                alt="{{ $banner->title }}" decoding="async"
                                                loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                                        </a>
                                        <div class="dash-poster-caption">
                                            <div>
                                                <p class="dash-eyebrow">KEGIATAN LABORATORIUM</p>
                                                <h3>{{ $banner->title }}</h3>
                                            </div>
                                            @if ($banner->link_url)
                                                <a href="{{ $banner->link_url }}" target="_blank"
                                                    rel="noopener noreferrer" class="dash-cta">
                                                    Lihat kegiatan <svg viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="1.7"
                                                        stroke-linecap="round" stroke-linejoin="round"
                                                        aria-hidden="true">
                                                        <path d="M7 17 17 7M7 7h10v10" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>

                        @if ($banners->count() > 1)
                            <div class="dash-gallery-toolbar">
                                <div class="dash-gallery-position">
                                    <span class="dash-gallery-count"
                                        x-text="String(current + 1).padStart(2, '0') + ' / ' + String(total).padStart(2, '0')">01
                                        / {{ $banners->count() }}</span>
                                    <div class="gallery-dots" aria-label="Pilih poster">
                                        @foreach ($banners as $banner)
                                            <button type="button" class="gallery-dot"
                                                aria-label="Lihat poster {{ $loop->iteration }}: {{ $banner->title }}"
                                                x-bind:class="{ 'is-active': current === {{ $loop->index }} }"
                                                x-bind:aria-current="current === {{ $loop->index }} ? 'true' : 'false'"
                                                x-on:click="select({{ $loop->index }})"></button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="dash-gallery-buttons">
                                    <button type="button" class="dash-icon-button dash-playback"
                                        x-on:click="paused = !paused; start()"
                                        x-bind:aria-label="paused ? 'Putar poster otomatis' : 'Jeda poster otomatis'"
                                        x-bind:aria-pressed="paused">
                                        <span x-show="!paused"><svg viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                                                stroke-linejoin="round" aria-hidden="true">
                                                <path d="M9 5v14M15 5v14" />
                                            </svg></span>
                                        <span x-show="paused" x-cloak><svg viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                                                stroke-linejoin="round" aria-hidden="true">
                                                <path d="m8 5 11 7-11 7Z" />
                                            </svg></span>
                                    </button>
                                    <button type="button" class="dash-icon-button" aria-label="Poster sebelumnya"
                                        x-on:click="select(current - 1)"><svg viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                                            stroke-linejoin="round" aria-hidden="true">
                                            <path d="m15 5-7 7 7 7" />
                                        </svg></button>
                                    <button type="button" class="dash-icon-button" aria-label="Poster berikutnya"
                                        x-on:click="select(current + 1)"><svg viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                                            stroke-linejoin="round" aria-hidden="true">
                                            <path d="m9 5 7 7-7 7" />
                                        </svg></button>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="dash-empty-events">
                        <span class="dash-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
                                aria-hidden="true">
                                <path d="m12 3 2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5Z" />
                            </svg></span>
                        <h3>Hal seru berikutnya<br>sedang disiapkan.</h3>
                        <p class="dash-muted">Webinar, pelatihan, dan kabar lab akan hadir di sini.</p>
                        @can('manage-lab')
                            <a href="{{ route('banners.index') }}" class="dash-cta">Tambah poster <svg
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M7 17 17 7M7 7h10v10" />
                                </svg></a>
                        @endcan
                    </div>
                @endif
            </section>

            <aside class="dash-sidebar" aria-label="Aktivitas laboratorium">
                <section aria-labelledby="dash-agenda-title">
                    <div class="dash-section-heading dash-agenda-heading">
                        <div>
                            <p class="dash-eyebrow">AGENDA</p>
                            <h2 id="dash-agenda-title">Praktikum hari ini</h2>
                        </div>
                        <a href="{{ route('schedules.index') }}" class="dash-icon-button"
                            aria-label="Lihat semua jadwal"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M7 17 17 7M7 7h10v10" />
                            </svg></a>
                    </div>
                    <p class="dash-agenda-note">Seluruh kelas · Waktu Indonesia Barat</p>
                    <div class="dash-agenda-list">
                        @forelse($schedules->take(5) as $schedule)
                            <article class="dash-agenda-item">
                                <div class="dash-agenda-time">
                                    <strong>{{ substr($schedule->starts_at, 0, 5) }}</strong>
                                    <span>{{ substr($schedule->ends_at, 0, 5) }}</span>
                                </div>
                                <div class="dash-agenda-detail">
                                    <h3>{{ $schedule->course_name }}</h3>
                                    <p>{{ $schedule->class_name }} · {{ $schedule->room }}</p>
                                    <p>{{ $schedule->instructor }}</p>
                                    <span class="dash-semester">{{ $schedule->semester }}</span>
                                </div>
                            </article>
                        @empty
                            <div class="dash-empty-agenda">
                                <p>Hari ini lebih luang.</p>
                                <span>Belum ada jadwal praktikum aktif. Waktunya eksplor materi baru.</span>
                            </div>
                        @endforelse
                    </div>
                    @if ($schedules->count() > 5)
                        <a href="{{ route('schedules.index') }}"
                            class="dash-text-link">{{ $schedules->count() - 5 }} jadwal lainnya <svg
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M7 17 17 7M7 7h10v10" />
                            </svg></a>
                    @endif
                </section>

                <section class="dash-screen" aria-labelledby="dash-screen-title">
                    <span class="dash-screen-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="13" rx="2" />
                            <path d="M8 21h8m-4-5v5" />
                        </svg></span>
                    <div>
                        <h2 id="dash-screen-title">Belajar satu layar.</h2>
                        <p class="dash-muted">Ikuti layar pengajar melalui jaringan lab.</p>
                        @if ($screenTaskConfigured)
                            <a href="{{ $screenTaskUrl }}" target="_blank" rel="noopener noreferrer"
                                class="dash-screen-link">Buka ScreenTask <svg viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <path d="M7 17 17 7M7 7h10v10" />
                                </svg></a>
                        @else
                            <p class="dash-screen-status">ScreenTask belum tersedia</p>
                        @endif
                    </div>
                </section>
            </aside>
        </div>

        <div class="dash-weather">
            <livewire:dashboard.weather lazy />
        </div>
    </div>
</x-layouts.app>
