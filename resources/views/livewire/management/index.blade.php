<section class="lab-management">
    <div class="management-heading">
        <div class="management-heading-top">
            <p class="management-eyebrow">
                PENGELOLAAN LABORATORIUM
            </p>

            <span
                class="management-role">
                {{ ucfirst(auth()->user()->role) }}
            </span>
        </div>

        <h1 class="management-title">
            Ruang pengelolaan lab
        </h1>

        <p class="management-description">
            Kelola kebutuhan praktikum dan aktivitas laboratorium
            dari satu tempat.
        </p>
    </div>

    <div class="management-grid">
        <article class="management-card">
            <h2 class="text-lg font-semibold">Banner kegiatan</h2>
            <p class="mt-2 text-sm text-muted">Upload poster webinar atau pelatihan, atur urutan, dan pilih banner yang aktif.</p>
            <a href="{{ route('banners.index') }}" class="management-link">Kelola banner →</a>
        </article>
        <article class="management-card">
            <h2 class="text-lg font-semibold">Materi praktikum</h2>

            <p class="mt-2 text-sm leading-relaxed text-muted">
                Upload dan kelola materi yang dapat diunduh mahasiswa.
            </p>

            <a href="{{ route('materials.index') }}"
                class="management-link">
                Kelola materi →
            </a>
        </article>

        <article class="management-card">
            <h2 class="text-lg font-semibold">Pengumpulan tugas</h2>

            <p class="mt-2 text-sm leading-relaxed text-muted">
                Kelola tugas, tentukan folder tujuan, dan lihat submission mahasiswa.
            </p>

            <a href="{{ route('assignments.index') }}"
                class="management-link">
                Kelola tugas →
            </a>
        </article>

        @can('manage-categories')
            <article class="management-card">
                <h2 class="text-lg font-semibold">Kategori pengumpulan</h2>

                <p class="mt-2 text-sm leading-relaxed text-muted">
                    Kelola kategori UAS, UTS, Tugas, dan kebutuhan lainnya.
                </p>

                <a href="{{ route('submission-categories.index') }}"
                    class="management-link">
                    Kelola kategori →
                </a>
            </article>
        @endcan

        <article class="management-card">
            <h2 class="text-lg font-semibold">Jadwal praktikum</h2>

            <p class="mt-2 text-sm leading-relaxed text-muted">
                Atur waktu, ruangan, dan pengajar untuk praktikum.
            </p>

            <a href="{{ route('schedules.index') }}"
                class="management-link">
                Kelola jadwal →
            </a>
        </article>

        @can('manage-users')
            <article class="management-card">
                <h2 class="text-lg font-semibold">
                    Pengguna
                </h2>

                <p class="mt-2 text-sm leading-relaxed text-muted">
                    Kelola akun dan role pengguna laboratorium.
                </p>

                <a href="{{ route('users.index') }}"
                    class="management-link">
                    Kelola pengguna →
                </a>
            </article>
        @endcan
    </div>
</section>
