<?php

namespace App\Livewire\Assignments;

use App\Models\Assignment;
use App\Models\Submission;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;
use App\Support\SubmissionDirectory;

class Show extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Locked]
    public int $assignmentId;

    #[Locked]
    public int $uploadIteration = 0;

    public $file = null;

    public string $notice = '';

    public function mount(Assignment $assignment): void
    {
        Gate::authorize('view-assignments');

        $this->assignmentId = $assignment->id;
    }

    private function filenamePart(string $value, int $length): string
    {
        return substr(Str::slug($value, ''), 0, $length) ?: 'Data';
    }

    private function removeFile(?string $path): void
    {
        if ($path === null) {
            return;
        }

        try {
            Storage::disk('submissions')->delete($path);
        } catch (Throwable $exception) {
            // Metadata submission yang berhasil tetap dipertahankan.
            report($exception);
        }
    }

    public function save(): void
    {
        Gate::authorize('submit-assignment');

        $this->notice = '';

        $this->validate([
            'file' => [
                'required',
                'file',
                'max:10240',
                'extensions:zip,rar,7z,pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
                'mimes:zip,rar,7z,pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
            ],
        ], [
            'file.required' => 'Pilih file yang akan dikumpulkan.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
            'file.extensions' => 'Ekstensi file tidak diizinkan.',
            'file.mimes' => 'Jenis file tidak sesuai format yang diizinkan.',
        ]);

        $user = auth()->user();

        if (! $user->nim) {
            $this->addError('file', 'Akun belum memiliki NIM. Hubungi Admin.');

            return;
        }

        $newPath = null;
        $oldPath = null;

        try {
            DB::transaction(function () use ($user, &$newPath, &$oldPath) {
                $assignment = Assignment::query()
                    ->lockForUpdate()
                    ->findOrFail($this->assignmentId);

                $existing = Submission::query()
                    ->where('assignment_id', $assignment->id)
                    ->where('user_id', $user->id)
                    ->first();

                if (! $assignment->acceptsSubmissions()) {
                    throw ValidationException::withMessages([
                        'file' => 'Pengumpulan sudah ditutup atau deadline berakhir.',
                    ]);
                }

                if ($existing && $assignment->due_at->isPast()) {
                    throw ValidationException::withMessages([
                        'file' => 'Upload ulang hanya diperbolehkan sebelum deadline.',
                    ]);
                }

                $extension = strtolower(
                    $this->file->getClientOriginalExtension()
                );

                $date = CarbonImmutable::now('Asia/Jakarta')
                    ->format('Y-m-d_H-i-s-u');

                $filename = implode('_', [
                    $this->filenamePart($user->nim, 30),
                    $this->filenamePart($user->name, 40),
                    $this->filenamePart(
                        $assignment->course_name . ' ' . $assignment->class_name,
                        50
                    ),
                    $date,
                ]) . '.' . $extension;

                // Folder unik mencegah file lama tertimpa sebelum transaksi berhasil.
                $directory = $assignment->submission_directory;

                if (!SubmissionDirectory::valid($directory)) {
                    throw ValidationException::withMessages([
                        'file' => 'Folder tujuan belum ditentukan atau sudah tidak tersedia. Hubungi pengelola tugas.',
                    ]);
                }

                $newPath = $this->file->storeAs(
                    $directory,
                    $filename,
                    'submissions'
                );

                $uploadedAt = CarbonImmutable::now('UTC');
                $isLate = $uploadedAt->greaterThan($assignment->due_at);

                // Periksa lagi setelah file selesai disalin ke penyimpanan.
                if ($isLate && (! $assignment->allow_late || $existing)) {
                    throw ValidationException::withMessages([
                        'file' => 'Deadline terlewati sebelum penyimpanan selesai.',
                    ]);
                }

                $oldPath = $existing?->file_path;

                $submission = $existing ?? new Submission;

                $submission->fill([
                    'assignment_id' => $assignment->id,
                    'user_id' => $user->id,
                    'submission_category_id' => $assignment->submission_category_id,
                    'original_filename' => $this->file->getClientOriginalName(),
                    'stored_filename' => $filename,
                    'file_path' => $newPath,
                    'file_size' => $this->file->getSize(),
                    'mime_type' => $this->file->getMimeType()
                        ?? 'application/octet-stream',
                    'uploaded_at' => $uploadedAt,
                    'deadline_at_upload' => $assignment->due_at,
                    'status' => $isLate ? 'late' : 'submitted',
                ])->save();
            });
        } catch (ValidationException $exception) {
            $this->removeFile($newPath);

            throw $exception;
        } catch (Throwable $exception) {
            $this->removeFile($newPath);
            report($exception);

            $this->addError(
                'file',
                'File belum berhasil disimpan. Coba kembali atau hubungi pengelola.'
            );

            return;
        }

        // Hapus versi lama hanya setelah metadata versi baru berhasil disimpan.
        $this->removeFile($oldPath);

        $this->reset('file');
        $this->uploadIteration++;

        $this->notice = $oldPath === null
            ? 'Tugas berhasil dikumpulkan.'
            : 'Submission berhasil diganti dengan file terbaru.';
    }

    public function render()
    {
        Gate::authorize('view-assignments');

        $assignment = Assignment::with('category')
            ->findOrFail($this->assignmentId);

        $mySubmission = Submission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', auth()->id())
            ->first();

        $submissions = Submission::query()
            ->with('user:id,name,nim')
            ->where('assignment_id', $assignment->id)
            ->orderByDesc('uploaded_at')
            ->paginate(15);

        $canUpload = Gate::allows('submit-assignment')
            && $assignment->acceptsSubmissions()
            && SubmissionDirectory::valid($assignment->submission_directory)
            && (!$mySubmission || !$assignment->due_at->isPast());

        return view('livewire.assignments.show', [
            'assignment' => $assignment,
            'mySubmission' => $mySubmission,
            'submissions' => $submissions,
            'canUpload' => $canUpload,
        ])->layout('components.layouts.app', [
            'title' => $assignment->title,
        ]);
    }
}
