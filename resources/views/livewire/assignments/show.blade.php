<section wire:poll.15s class="lab-tasks task-detail">
    <a href="{{ route('assignments.index') }}" class="task-back">
        ← Daftar tugas
    </a>

    @if ($notice !== '')
        <div role="status"
            class="tasks-notice">
            {{ $notice }}
        </div>
    @endif

    <div class="task-detail-layout">
        <article class="task-overview">
            <p class="tasks-eyebrow">
                {{ $assignment->category->name }}
                · {{ $assignment->semester }}
            </p>

            <h1 class="tasks-title">
                {{ $assignment->title }}
            </h1>

            <p class="tasks-description">
                {{ $assignment->course_name }}
                · Kelas {{ $assignment->class_name }}
            </p>

            <p class="task-deadline">
                Deadline:
                {{ $assignment->due_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                WIB
            </p>

            <p class="task-collection-status">
                {{ $assignment->collectionStatus() }}
            </p>

            @if ($assignment->instructions)
                <div class="task-instructions">
                    <h2 class="font-semibold">Instruksi</h2>

                    <p class="mt-3 whitespace-pre-line break-words leading-relaxed text-muted">
                        {{ $assignment->instructions }}</p>
                </div>
            @endif
        </article>

        @can('submit-assignment')
            <section class="task-submission">
                <h2 class="text-xl font-semibold">Pengumpulan kamu</h2>

                <p @class([
                    'mt-3 text-sm font-medium',
                    'text-muted' => !$mySubmission,
                    'text-success' => $mySubmission?->status === 'submitted',
                    'text-accent' => $mySubmission?->status === 'late',
                ])>
                    {{ $mySubmission?->statusLabel() ?? 'Belum mengumpulkan' }}
                </p>

                @if ($mySubmission)
                    <div class="task-submitted-file">
                        <p class="break-all text-sm font-medium">
                            {{ $mySubmission->stored_filename }}
                        </p>

                        <p class="mt-2 text-xs leading-relaxed text-muted">
                            {{ number_format($mySubmission->file_size / 1048576, 2) }} MB
                            · Dikirim
                            {{ $mySubmission->uploaded_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}
                            WIB
                        </p>
                    </div>
                @endif

                @if ($canUpload)
                    <form wire:submit="save" class="mt-6 space-y-4">
                        <div>
                            <label for="submission-file" class="form-label">
                                {{ $mySubmission ? 'File pengganti' : 'File tugas' }}
                            </label>

                            <input wire:model="file" wire:key="submission-file-{{ $uploadIteration }}" id="submission-file"
                                type="file" accept=".zip,.rar,.7z,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt" required
                                class="form-input task-file-input">

                            <p class="mt-2 text-xs leading-relaxed text-muted">
                                Maksimal 10 MB. Source code dapat dibungkus dalam ZIP.
                                File baru menggantikan submission sebelumnya.
                            </p>

                            <p wire:loading wire:target="file" role="status" class="mt-2 text-sm text-secondary">
                                Menyiapkan file. Tekan tombol kumpulkan setelah selesai.
                            </p>
                        </div>

                        <button type="submit" wire:loading.attr="disabled" wire:target="file,save" class="button-primary">
                            <span wire:loading.remove wire:target="save">
                                {{ $mySubmission ? 'Ganti submission' : 'Kumpulkan tugas' }}
                            </span>

                            <span wire:loading wire:target="save">
                                Menyimpan...
                            </span>
                        </button>
                    </form>
                @else
                    <p class="mt-5 text-sm text-muted">
                        @if (!\App\Support\SubmissionDirectory::valid($assignment->submission_directory))
                            Folder tujuan belum tersedia. Hubungi pengelola tugas.
                        @elseif ($mySubmission && $assignment->due_at->isPast())
                            Batas waktu upload ulang sudah berakhir.
                        @else
                            Pengumpulan saat ini ditutup.
                        @endif
                    </p>
                @endif

                @error('file')
                    <p role="alert" class="form-error">{{ $message }}</p>
                @enderror
            </section>
        @endcan

    </div>

    @can('manage-lab')
        <div class="task-destination">
            <p class="text-xs text-muted">Folder tujuan pengumpulan</p>

            @if ($assignment->submission_directory)
                <p class="mt-2 break-all font-mono text-xs text-secondary">
                    {{ rtrim(config('filesystems.disks.submissions.root'), '/\\') }}/{{ $assignment->submission_directory }}
                </p>
            @else
                <p class="mt-2 text-sm text-accent">
                    Belum ditentukan. Pilih folder melalui Edit tugas.
                </p>
            @endif
        </div>
    @endcan
</section>
