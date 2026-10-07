<section class="lab-schedules">
    <div class="schedule-heading">
        <div>
            <p class="schedule-eyebrow">
                AKTIVITAS LABORATORIUM
            </p>

            <h1 class="schedule-title">Jadwal praktikum</h1>

            <p class="schedule-description">
                Jadwal mingguan laboratorium. Seluruh jam menggunakan WIB.
            </p>
        </div>

        @can('manage-lab')
            <button
                type="button"
                wire:click="create"
                class="button-primary"
            >
                + Tambah jadwal
            </button>
        @endcan
    </div>

    @if ($notice !== '')
        <div
            role="status"
            class="schedule-notice"
        >
            {{ $notice }}
        </div>
    @endif

    @error('operation')
        <div
            role="alert"
            class="schedule-alert"
        >
            {{ $message }}
        </div>
    @enderror

    @can('manage-lab')
        @if ($showForm)
            <section
                class="schedule-editor"
            >
                <h2 class="text-xl font-semibold">
                    {{ $editingId === null ? 'Tambah jadwal' : 'Edit jadwal' }}
                </h2>

                <form wire:submit="save" class="mt-6 space-y-5">
                    <div class="grid gap-5 md:grid-cols-2">
                        @foreach ([
                            'course_name' => ['Mata kuliah', 'Pemrograman Web', 100],
                            'class_name' => ['Kelas', 'A', 50],
                            'semester' => ['Semester', 'Ganjil 2026/2027', 50],
                            'room' => ['Ruangan', 'LAB 1', 50],
                            'instructor' => ['Pengajar / asisten', 'Nama pengajar atau asisten', 150],
                        ] as $field => [$label, $placeholder, $limit])
                            <div>
                                <label
                                    for="schedule-{{ $field }}"
                                    class="form-label"
                                >
                                    {{ $label }}
                                </label>

                                <input
                                    wire:model="{{ $field }}"
                                    id="schedule-{{ $field }}"
                                    type="text"
                                    maxlength="{{ $limit }}"
                                    placeholder="{{ $placeholder }}"
                                    required
                                    class="form-input"
                                >

                                @error($field)
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach

                        <div>
                            <label for="schedule-day" class="form-label">
                                Hari
                            </label>

                            <select
                                wire:model="day_of_week"
                                id="schedule-day"
                                required
                                class="form-input"
                            >
                                @foreach ($days as $number => $label)
                                    <option value="{{ $number }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            @error('day_of_week')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="schedule-start" class="form-label">
                                Jam mulai (WIB)
                            </label>

                            <input
                                wire:model="starts_at"
                                id="schedule-start"
                                type="time"
                                required
                                class="form-input"
                            >

                            @error('starts_at')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="schedule-end" class="form-label">
                                Jam selesai (WIB)
                            </label>

                            <input
                                wire:model="ends_at"
                                id="schedule-end"
                                type="time"
                                required
                                class="form-input"
                            >

                            @error('ends_at')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <label class="schedule-toggle">
                        <input
                            wire:model="is_active"
                            type="checkbox"
                            class="h-4 w-4 accent-primary"
                        >

                        <span class="text-sm">
                            Jadwal aktif dan ditampilkan kepada mahasiswa
                        </span>
                    </label>

                    <div class="schedule-form-actions">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="button-primary"
                        >
                            <span wire:loading.remove wire:target="save">
                                Simpan jadwal
                            </span>
                            <span wire:loading wire:target="save">
                                Menyimpan...
                            </span>
                        </button>

                        <button
                            type="button"
                            wire:click="cancelEdit"
                            class="schedule-button schedule-button-quiet"
                        >
                            Batal
                        </button>
                    </div>
                </form>
            </section>
        @endif
    @endcan

    <div class="schedule-list-heading">
        <h2>Agenda mingguan</h2>
        <span>Zona waktu WIB</span>
    </div>

    <div class="schedule-filters">
        <div class="schedule-search">
            <label for="schedule-search" class="form-label">Cari jadwal</label>

            <input
                wire:model.live.debounce.300ms="search"
                id="schedule-search"
                type="search"
                maxlength="150"
                placeholder="Mata kuliah, kelas, ruangan, pengajar, atau semester..."
                class="form-input"
            >
        </div>

        <div>
            <label for="schedule-day-filter" class="form-label">Hari</label>

            <select
                wire:model.live="dayFilter"
                id="schedule-day-filter"
                class="form-input"
            >
                <option value="">Semua hari</option>

                @foreach ($days as $number => $label)
                    <option value="{{ $number }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="schedule-list">
        @forelse ($schedules as $schedule)
            <article
                wire:key="schedule-{{ $schedule->id }}"
                class="schedule-row"
            >
                <div class="schedule-time">
                    <span class="schedule-day">{{ $days[$schedule->day_of_week] }}</span>
                    <p>{{ substr($schedule->starts_at, 0, 5) }} <span>–</span> {{ substr($schedule->ends_at, 0, 5) }}</p>
                    <span class="schedule-timezone">WIB</span>
                    @if (!$schedule->is_active)
                        <span class="schedule-inactive">Nonaktif</span>
                    @endif
                </div>

                <div class="schedule-info">
                    <h2 class="schedule-course">
                        {{ $schedule->course_name }}
                    </h2>

                    <p class="mt-2 text-sm text-muted">
                        Kelas {{ $schedule->class_name }}
                        · {{ $schedule->room }}
                    </p>

                    <p class="mt-2 text-sm text-muted">
                        {{ $schedule->semester }}
                    </p>

                    <p class="schedule-instructor">
                        Pengajar / asisten: {{ $schedule->instructor }}
                    </p>

                </div>

                @can('manage-lab')
                    <div class="schedule-actions">
                        <button
                            type="button"
                            wire:click="edit({{ $schedule->id }})"
                            wire:loading.attr="disabled"
                            wire:target="edit,save,delete"
                            class="schedule-button schedule-button-quiet"
                        >
                            Edit
                        </button>

                        <button
                            type="button"
                            wire:click="delete({{ $schedule->id }})"
                            wire:confirm="Hapus jadwal ini? Tindakan ini tidak bisa dibatalkan."
                            wire:loading.attr="disabled"
                            wire:target="edit,save,delete"
                            class="schedule-button schedule-button-danger"
                        >
                            Hapus
                        </button>
                    </div>
                @endcan
            </article>
        @empty
            <div class="schedule-empty">
                Belum ada jadwal yang sesuai.
            </div>
        @endforelse
    </div>
</section>