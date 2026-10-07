@props(['title' => 'Portal Laboratorium'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — dasar.com</title>
    <x-theme-script />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    @php
        $navigation = [];

        if (auth()->check()) {
            $navigation[] = ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard'];
        }

        $navigation[] = ['label' => 'Materi', 'route' => 'materials.index', 'active' => 'materials.*'];

        if (auth()->check()) {
            if (\Illuminate\Support\Facades\Gate::allows('view-assignments')) {
                $navigation[] = ['label' => 'Tugas', 'route' => 'assignments.index', 'active' => 'assignments.*'];
            }
            if (\Illuminate\Support\Facades\Gate::allows('view-schedules')) {
                $navigation[] = ['label' => 'Jadwal', 'route' => 'schedules.index', 'active' => 'schedules.*'];
            }
            if (\Illuminate\Support\Facades\Gate::allows('manage-lab')) {
                $navigation[] = ['label' => 'Pengelolaan', 'route' => 'management.index', 'active' => 'management.*'];
            }
        }
    @endphp

    <header class="site-header lab-navbar"
        x-data="{ open: false }"
        x-on:click.outside="open = false"
        x-on:keydown.escape.window="if (open) { open = false; $refs.menuToggle.focus(); }"
        x-on:resize.window="if (window.matchMedia('(min-width: 1024px)').matches) open = false">
        <nav class="lab-nav-shell" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="lab-nav-brand" aria-label="dasar.com — halaman utama">
                <span>dasar<span class="lab-nav-brand-dot">.</span>com</span>
                <small>PORTAL LABORATORIUM</small>
            </a>

            <button type="button" class="lab-nav-toggle" x-ref="menuToggle"
                aria-controls="lab-navigation-menu" aria-expanded="false"
                x-bind:aria-expanded="open" x-bind:aria-label="open ? 'Tutup menu navigasi' : 'Buka menu navigasi'"
                x-on:click="open = !open">
                <svg x-show="!open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                <svg x-show="open" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M6 18 18 6" /></svg>
            </button>

            <div id="lab-navigation-menu" class="lab-nav-menu" x-bind:class="{ 'is-open': open }">
                <div class="lab-nav-links">
                    @foreach($navigation as $item)
                        <a href="{{ route($item['route']) }}" class="lab-nav-link"
                            @if(request()->routeIs($item['active'])) aria-current="page" @endif
                            x-on:click="open = false">{{ $item['label'] }}</a>
                    @endforeach
                </div>

                <div class="lab-nav-account">
                    @auth
                        <a href="{{ route('profile.index') }}" class="lab-nav-profile"
                            @if(request()->routeIs('profile.*')) aria-current="page" @endif
                            x-on:click="open = false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="8" r="3.5" /><path d="M5 20a7 7 0 0 1 14 0" /></svg>
                            <span>Profil</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="lab-nav-logout">
                                <span>Keluar</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 4H4v16h5m5-13 5 5-5 5m-6-5h11" /></svg>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="lab-nav-profile"
                            @if(request()->routeIs('login')) aria-current="page" @endif>Masuk</a>
                        <a href="{{ route('register') }}" class="lab-nav-register"
                            @if(request()->routeIs('register')) aria-current="page" @endif>Daftar akun</a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl px-5 py-8 sm:px-6 sm:py-10">{{ $slot }}</main>
    <footer class="mx-auto flex max-w-6xl flex-wrap justify-between gap-3 px-6 pb-8 text-xs text-muted">
        <span>© {{ now()->year }} dasar.com</span><span>Ruang belajar. Ruang berkembang.</span>
    </footer>

    <div class="floating-theme-control">
        <x-theme-toggle />
    </div>
    @livewireScripts
</body>
</html>
