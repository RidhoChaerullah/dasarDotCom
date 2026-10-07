<?php

namespace App\Livewire\SubmissionCategories;

use App\Models\SubmissionCategory;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Database\QueryException;

class Index extends Component
{
    #[Locked]
    public ?int $editingId = null;

    public string $name = '';
    public string $description = '';
    public bool $isActive = true;
    public string $notice = '';

    public function save(): void
    {
        Gate::authorize('manage-categories');

        $category = $this->editingId === null
            ? new SubmissionCategory()
            : SubmissionCategory::findOrFail($this->editingId);

        $this->name = trim($this->name);
        $this->description = trim($this->description);

        $uniqueName = Rule::unique('submission_categories', 'name');

        if ($category->exists) {
            $uniqueName->ignore($category);
        }

        $this->validate([
            'name' => ['required', 'string', 'max:100', $uniqueName],
            'description' => ['nullable', 'string', 'max:1000'],
            'isActive' => ['boolean'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'description.max' => 'Deskripsi maksimal 1.000 karakter.',
        ]);

        $isNew = !$category->exists;

        $category->fill([
            'name' => $this->name,
            'description' => $this->description !== ''
                ? $this->description
                : null,
            'is_active' => $this->isActive,
        ])->save();

        $this->cancelEdit();

        $this->notice = $isNew
            ? 'Kategori berhasil ditambahkan.'
            : 'Kategori berhasil diperbarui.';
    }

    public function edit(int $id): void
    {
        Gate::authorize('manage-categories');

        $category = SubmissionCategory::findOrFail($id);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->isActive = $category->is_active;

        $this->notice = '';
        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'name', 'description', 'isActive');
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        Gate::authorize('manage-categories');

        $this->notice = '';
        $this->resetValidation('operation');

        $category = SubmissionCategory::findOrFail($id);

        $usedByAssignments = Assignment::query()
            ->where('submission_category_id', $category->id)
            ->exists();

        $usedBySubmissions = Submission::query()
            ->where('submission_category_id', $category->id)
            ->exists();

        if ($usedByAssignments || $usedBySubmissions) {
            $this->addError(
                'operation',
                'Kategori sudah dipakai oleh tugas atau submission. Nonaktifkan melalui Edit agar riwayat tetap tersimpan.'
            );

            return;
        }

        try {
            $category->delete();
        } catch (QueryException $exception) {
            // Lindungi jika kategori mulai digunakan sesaat setelah pemeriksaan.
            if ((int) ($exception->errorInfo[1] ?? 0) === 1451) {
                $this->addError(
                    'operation',
                    'Kategori sudah digunakan dan tidak dapat dihapus.'
                );

                return;
            }

            throw $exception;
        }

        if ($this->editingId === $id) {
            $this->cancelEdit();
        }

        $this->notice = 'Kategori berhasil dihapus.';
    }

    public function render()
    {
        Gate::authorize('manage-categories');

        return view('livewire.submission-categories.index', [
            'categories' => SubmissionCategory::query()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
        ])->layout('components.layouts.app', [
            'title' => 'Kategori Pengumpulan',
        ]);
    }
}
