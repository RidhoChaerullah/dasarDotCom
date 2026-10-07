<?php

namespace App\Livewire\Assignments;

use App\Models\Assignment;
use App\Models\SubmissionCategory;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\QueryException;
use App\Support\SubmissionDirectory;
use Illuminate\Validation\Rule;

class Index extends Component
{
    use WithPagination;

    #[Locked]
    public ?int $editingId = null;

    public bool $showForm = false;
    public string $submission_category_id = '';
    public string $title = '';
    public string $course_name = '';
    public string $class_name = '';
    public string $semester = '';
    public string $instructions = '';
    public string $due_at = '';
    public bool $is_open = true;
    public bool $allow_late = false;
    public string $search = '';
    public string $notice = '';
    public string $submission_directory = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        Gate::authorize('manage-lab');

        $this->cancelEdit();
        $this->showForm = true;
        $this->notice = '';
    }

    public function edit(int $id): void
    {
        Gate::authorize('manage-lab');

        $assignment = Assignment::findOrFail($id);

        $this->editingId = $assignment->id;
        $this->submission_category_id = (string) $assignment->submission_category_id;
        $this->title = $assignment->title;
        $this->course_name = $assignment->course_name;
        $this->class_name = $assignment->class_name;
        $this->semester = $assignment->semester;
        $this->submission_directory = $assignment->submission_directory ?? '';
        $this->instructions = $assignment->instructions ?? '';

        $this->due_at = $assignment->due_at
            ->setTimezone('Asia/Jakarta')
            ->format('Y-m-d\TH:i');

        $this->is_open = $assignment->is_open;
        $this->allow_late = $assignment->allow_late;
        $this->showForm = true;
        $this->notice = '';

        $this->resetValidation();
    }

    public function save(): void
    {
        Gate::authorize('manage-lab');

        $assignment = $this->editingId === null
            ? new Assignment()
            : Assignment::findOrFail($this->editingId);

        foreach (
            [
                'title',
                'course_name',
                'class_name',
                'semester',
                'instructions',
            ] as $field
        ) {
            $this->{$field} = trim($this->{$field});
        }

        $data = $this->validate([
            'submission_directory' => [
                'required',
                'string',
                'max:255',
                Rule::in(SubmissionDirectory::options()),
            ],
            'submission_category_id' => [
                'required',
                'integer',
                'exists:submission_categories,id',
            ],
            'title' => ['required', 'string', 'max:150'],
            'course_name' => ['required', 'string', 'max:100'],
            'class_name' => ['required', 'string', 'max:50'],
            'semester' => ['required', 'string', 'max:50'],
            'instructions' => ['nullable', 'string', 'max:10000'],
            'due_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'is_open' => ['boolean'],
            'allow_late' => ['boolean'],
        ], [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute maksimal :max karakter.',
            'due_at.date_format' => 'Tanggal dan jam deadline tidak valid.',
            'submission_category_id.exists' => 'Kategori tidak tersedia.',
            'submission_directory.required' => 'Pilih folder tujuan pengumpulan.',
            'submission_directory.in' => 'Folder tidak tersedia. Pilih folder yang ada di server.',
        ], [
            'submission_category_id' => 'Kategori',
            'title' => 'Judul tugas',
            'course_name' => 'Mata kuliah',
            'class_name' => 'Kelas',
            'semester' => 'Semester',
            'instructions' => 'Instruksi',
            'due_at' => 'Deadline',
        ]);

        $category = SubmissionCategory::findOrFail(
            $data['submission_category_id']
        );

        // Kategori nonaktif boleh dipertahankan oleh tugas lama,
        // tetapi tidak boleh dipilih untuk tugas baru.
        $keepingExistingCategory = $assignment->exists
            && (int) $assignment->submission_category_id === $category->id;

        if (!$category->is_active && !$keepingExistingCategory) {
            $this->addError(
                'submission_category_id',
                'Pilih kategori yang masih aktif.'
            );

            return;
        }

        $isNew = !$assignment->exists;

        // Input menggunakan WIB, penyimpanan menggunakan UTC.
        // Seluruh detik dalam menit yang dipilih masih diterima.
        $data['due_at'] = CarbonImmutable::createFromFormat(
            '!Y-m-d\TH:i',
            $data['due_at'],
            'Asia/Jakarta'
        )->endOfMinute()->utc();

        $data['instructions'] = $data['instructions'] !== ''
            ? $data['instructions']
            : null;

        $assignment->fill($data);

        if ($isNew) {
            $assignment->created_by = auth()->id();
        }

        $assignment->save();

        $this->cancelEdit();
        $this->resetPage();

        $this->notice = $isNew
            ? 'Tugas berhasil dibuat.'
            : 'Tugas berhasil diperbarui.';
    }

    public function cancelEdit(): void
    {
        $this->reset(
            'editingId',
            'showForm',
            'submission_category_id',
            'title',
            'course_name',
            'class_name',
            'semester',
            'instructions',
            'due_at',
            'is_open',
            'allow_late',
            'submission_directory',
        );

        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        Gate::authorize('manage-lab');

        $this->notice = '';
        $this->resetValidation('operation');

        $assignment = Assignment::findOrFail($id);

        if ($assignment->submissions()->exists()) {
            $this->addError(
                'operation',
                'Tugas sudah memiliki submission dan tidak dapat dihapus. Untuk menutup pengumpulan, buka Edit lalu nonaktifkan "Buka pengumpulan".'
            );

            return;
        }

        try {
            $assignment->delete();
        } catch (QueryException $exception) {
            // Antisipasi submission yang masuk saat proses penghapusan.
            if ((int) ($exception->errorInfo[1] ?? 0) === 1451) {
                $this->addError(
                    'operation',
                    'Tugas sudah memiliki data terkait dan tidak dapat dihapus.'
                );

                return;
            }

            throw $exception;
        }

        if ($this->editingId === $id) {
            $this->cancelEdit();
        }

        $this->resetPage();
        $this->notice = 'Tugas berhasil dihapus.';
    }

    public function render()
    {
        Gate::authorize('view-assignments');

        $search = mb_substr(trim($this->search), 0, 150);

        $assignments = Assignment::query()
            ->with([
                'category',
                'submissions' => fn($query) => $query->where(
                    'user_id',
                    auth()->id()
                ),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('course_name', 'like', "%{$search}%")
                        ->orWhere('class_name', 'like', "%{$search}%")
                        ->orWhere('semester', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(10);

        return view('livewire.assignments.index', [
            'assignments' => $assignments,
            'categories' => SubmissionCategory::orderBy('name')->get(),
            'directories' => SubmissionDirectory::options(),
        ])->layout('components.layouts.app', [
            'title' => 'Pengumpulan Tugas',
        ]);
    }
}
