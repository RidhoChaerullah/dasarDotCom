<section class="lab-tasks task-index">
    <div class="tasks-heading">
        <div>
            <p class="tasks-eyebrow">
                AKTIVITAS PRAKTIKUM
            </p>

            <h1 class="tasks-title">Pengumpulan tugas</h1>

            <p class="tasks-description">
                Temukan tugas sesuai mata kuliah, kelas, dan semester kamu.
            </p>
        </div>

        @can('manage-lab')
            <button type="button" wire:click="create" class="button-primary">
                + Buat tugas
            </button>
        @endcan
    </div>

    @if ($notice !== '')
        <div role="status"
            class="tasks-notice">
            {{ $notice }}
        </div>
    @endif

    @error('operation')
        <div role="alert"
            class="tasks-alert">
            {{ $message }}
        </div>
    @enderror

    @can('manage-lab')
        @if ($showForm)
            <section class="task-editor">
                <h2 class="text-xl font-semibold">
                    {{ $editingId === null ? 'Buat tugas baru' : 'Edit tugas' }}
                </h2>

                <form wire:submit="save" class="mt-6 space-y-5">
                    <div>
                        <label for="task-directory" class="form-label">
                            Folder tujuan pengumpulan
                        </label>

                        <select wire:model="submission_directory" id="task-directory" required class="form-input">
                            <option value="">Pilih folder mata kuliah / kelas</option>

                            @foreach ($directories as $directory)
                                <option value="{{ $directory }}">
                                    {{ str_replace('/', ' / ', $directory) }}
                                </option>
                            @endforeach
                        </select>

                        <p class="mt-2 text-xs leading-relaxed text-muted">
                            Semua file untuk tugas ini disimpan di folder yang dipilih.
                        </p>

                        @if (count($directories) === 0)
                            <p class="mt-2 text-sm text-accent">
                                Belum ada folder tujuan. Buat folder mata kuliah dan kelas
                                di komputer server, lalu muat ulang halaman.
                            </p>
                        @endif

                        @error('submission_directory')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        @foreach ([
            'title' => ['Judul tugas', 'Tugas HTML Pertemuan 1', 150],
            'course_name' => ['Mata kuliah', 'Pemrograman Web', 100],
            'class_name' => ['Kelas', 'A', 50],
            'semester' => ['Semester', 'Ganjil 2026/2027', 50],
        ] as $field => [$label, $placeholder, $limit])
                            <div>
                                <label for="task-{{ $field }}" class="form-label">
                                    {{ $label }}
                                </label>

                                <input wire:model="{{ $field }}" id="task-{{ $field }}" type="text"
                                    maxlength="{{ $limit }}" placeholder="{{ $placeholder }}" required
                                    class="form-input">

                                @error($field)
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach

                        <div>
                            <label for="task-category" class="form-label">
                                Kategori
                            </label>

                            <select wire:model="submission_category_id" id="task-category" required class="form-input">
                                <option value="">Pilih kategori</option>

                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @disabled(!$category->is_active && (string) $category->id !== $submission_category_id)>
                                        {{ $category->name }}
                                        {{ !$category->is_active ? '(nonaktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>

                            @error('submission_category_id')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="task-deadline" class="form-label">
                                Deadline (WIB)
                            </label>

                            <input wire:model="due_at" id="task-deadline" type="datetime-local" required
                                class="form-input">

                            @error('due_at')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="task-instructions" class="form-label">
                            Instruksi tugas
                        </label>

                        <textarea wire:model="instructions" id="task-instructions" rows="5" maxlength="10000"
                            placeholder="Tuliskan instruksi dan ketentuan pengumpulan..." class="form-input resize-y"></textarea>

                        @error('instructions')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="task-options">
                        <label class="flex cursor-pointer items-center gap-3">
                            <input wire:model="is_open" type="checkbox" class="h-4 w-4 accent-primary">
                            <span class="text-sm">Buka pengumpulan</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3">
                            <input wire:model="allow_late" type="checkbox" class="h-4 w-4 accent-primary">
                            <span class="text-sm">
                                Izinkan pengumpulan setelah deadline
                            </span>
                        </label>
                    </div>

                    <div class="task-form-actions">
                        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="button-primary">
                            <span wire:loading.remove wire:target="save">
                                Simpan tugas
                            </span>
                            <span wire:loading wire:target="save">
                                Menyimpan...
                            </span>
                        </button>

                        <button type="button" wire:click="cancelEdit" class="task-button task-button-quiet">
                            Batal
                        </button>
                    </div>
                </form>
            </section>
        @endif
    @endcan

    <div class="tasks-toolbar">
        <div>
            <h2>Daftar tugas</h2>
            <p>Periksa kelas dan tenggat sebelum mengumpulkan.</p>
        </div>
        <div class="tasks-search">
            <label for="task-search" class="form-label">Cari tugas</label>
            <input wire:model.live.debounce.300ms="search" id="task-search" type="search" maxlength="150"
                placeholder="Judul, mata kuliah, kelas, semester…" class="form-input">
        </div>
    </div>

    <div class="task-list">
        @forelse ($assignments as $assignment)
            <article wire:key="assignment-{{ $assignment->id }}"
                class="task-card">
                <div class="task-badges">
                    <span class="task-badge task-category">
                        {{ $assignment->category->name }}
                    </span>

                    <span @class([
                        'task-badge',
                        'bg-emerald-400/10 text-success' => $assignment->acceptsSubmissions(),
                        'bg-slate-400/10 text-muted' => !$assignment->acceptsSubmissions(),
                    ])>
                        {{ $assignment->collectionStatus() }}
                    </span>
                </div>

                <h2 class="mt-4 break-words text-xl font-semibold">
                    {{ $assignment->title }}
                </h2>

                <p class="mt-2 break-words text-sm text-muted">
                    {{ $assignment->course_name }}
                    · Kelas {{ $assignment->class_name }}
                    · {{ $assignment->semester }}
                </p>

                <p class="task-deadline">
                    Deadline:
                    {{ $assignment->due_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                    WIB
                </p>

                @if ($assignment->instructions)
                    <details class="task-instructions-preview">
                        <summary class="cursor-pointer text-sm text-secondary">
                            Lihat instruksi
                        </summary>

                        <p class="mt-3 whitespace-pre-line break-words text-sm leading-relaxed text-muted">
                            {{ $assignment->instructions }}</p>
                    </details>
                @endif

                @can('submit-assignment')
                    @php
                        $ownSubmission = $assignment->submissions->first();
                    @endphp

                    <p @class([
                        'mt-4 text-sm font-medium',
                        'text-muted' => !$ownSubmission,
                        'text-success' => $ownSubmission?->status === 'submitted',
                        'text-accent' => $ownSubmission?->status === 'late',
                    ])>
                        Status kamu:
                        {{ $ownSubmission?->statusLabel() ?? 'Belum mengumpulkan' }}
                    </p>
                @endcan

                <div class="task-card-actions">
                    <a href="{{ route('assignments.show', $assignment) }}" class="button-primary">
                        Buka tugas
                    </a>

                    @can('manage-lab')
                        <button type="button" wire:click="edit({{ $assignment->id }})"
                            class="task-button task-button-quiet">
                            Edit tugas
                        </button>
                        <button type="button" wire:click="delete({{ $assignment->id }})"
                            wire:confirm="Hapus tugas ini? Tindakan ini tidak bisa dibatalkan."
                            wire:loading.attr="disabled" wire:target="delete"
                            class="task-button task-button-danger">
                            Hapus tugas
                        </button>
                    @endcan
                </div>
            </article>
        @empty
            <div
                class="tasks-empty">
                {{ trim($search) !== '' ? 'Tidak ada tugas yang cocok.' : 'Belum ada tugas yang dibuat.' }}
            </div>
        @endforelse
    </div>

    <div class="tasks-pagination">
        {{ $assignments->links() }}
    </div>
</section>
