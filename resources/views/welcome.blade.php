<x-layouts.app title="Portal Laboratorium">
    <section class="panel welcome-hero p-8 sm:p-14">
        <p class="eyebrow">PORTAL LABORATORIUM KOMPUTER</p>
        <h1 class="mt-6 text-5xl font-bold tracking-tight sm:text-7xl">Belajar dimulai<br>dari <span class="text-secondary">dasar.</span></h1>
        <p class="mt-6 max-w-xl text-lg leading-relaxed text-muted">Materi praktikum, pengumpulan tugas, dan kegiatan laboratorium. Satu ruang untuk terus berkembang.</p>
        <div class="mt-8 flex flex-wrap gap-3"><a href="{{ route('materials.index') }}" class="button-primary">Jelajahi materi →</a>
            @auth <a href="{{ route('dashboard') }}" class="button-secondary">Buka dashboard</a>
            @else <a href="{{ route('login') }}" class="button-secondary">Masuk ke akun</a> @endauth
        </div>
        <p class="mt-6 text-sm text-muted">Materi bisa diakses tanpa login. Masuk untuk mengumpulkan tugas.</p>
    </section>
</x-layouts.app>
