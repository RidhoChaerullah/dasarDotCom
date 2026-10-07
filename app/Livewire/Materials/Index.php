<?php

namespace App\Livewire\Materials;

use App\Support\MaterialDirectory;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    #[Url]
    public string $path = '';

    public string $search = '';
    public string $title = '';
    public $file = null;

    #[Locked]
    public ?string $editingPath = null;

    public string $editingTitle = '';

    #[Locked]
    public int $uploadIteration = 0;

    public string $notice = '';

    private function directory(): MaterialDirectory
    {
        return app(MaterialDirectory::class);
    }

    private function childPath(string $name): string
    {
        return $this->path === '' ? $name : $this->path . '/' . $name;
    }

    private function safeBaseName(string $name): string
    {
        $name = substr(Str::slug($name), 0, 120);

        if ($name === '') {
            $name = 'materi';
        }

        if (preg_match('/^(con|prn|aux|nul|com[0-9]|lpt[0-9])$/i', $name)) {
            $name = 'materi-' . $name;
        }

        return $name;
    }

    public function openFolder(string $path): void
    {
        Gate::authorize('view-materials');

        $absolute = $this->directory()->resolve($path);

        abort_unless($absolute && is_dir($absolute), 404);

        $this->path = $path;
        $this->reset('search', 'title', 'file', 'notice');
        $this->uploadIteration++;
        $this->cancelEdit();
        $this->resetValidation();
    }

    public function save(): void
    {
        Gate::authorize('manage-lab');

        $absolute = $this->directory()->resolve($this->path);

        if (!$absolute || !is_dir($absolute)) {
            $this->addError('file', 'Folder tujuan sudah tidak tersedia.');

            return;
        }

        $this->title = trim($this->title);

        $this->validate([
            'title' => ['required', 'string', 'max:150'],
            'file' => [
                'required',
                'file',
                'max:10240',
                'extensions:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,7z,txt',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,7z,txt',
            ],
        ], [
            'title.required' => 'Nama materi wajib diisi.',
            'title.max' => 'Nama materi maksimal 150 karakter.',
            'file.required' => 'Pilih file terlebih dahulu.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
            'file.extensions' => 'Ekstensi file tidak diizinkan.',
            'file.mimes' => 'Jenis file tidak sesuai format yang diizinkan.',
        ]);

        $extension = strtolower($this->file->getClientOriginalExtension());

        $filename = $this->safeBaseName($this->title)
            . '-' . Str::lower(Str::random(8))
            . '.' . $extension;

        Storage::disk('materials')->putFileAs(
            $this->path,
            $this->file,
            $filename
        );

        $this->reset('title', 'file', 'search');
        $this->uploadIteration++;

        $this->notice = 'File berhasil ditambahkan ke folder ini.';
    }

    public function edit(string $path): void
    {
        Gate::authorize('manage-lab');

        $absolute = $this->directory()->resolve($path);

        abort_unless($absolute && is_file($absolute), 404);

        $this->editingPath = $path;
        $this->editingTitle = pathinfo($path, PATHINFO_FILENAME);

        $this->resetValidation('editingTitle');
    }

    public function updateTitle(): void
    {
        Gate::authorize('manage-lab');

        $this->editingTitle = trim($this->editingTitle);

        $this->validate([
            'editingTitle' => ['required', 'string', 'max:150'],
        ], [
            'editingTitle.required' => 'Nama file wajib diisi.',
            'editingTitle.max' => 'Nama file maksimal 150 karakter.',
        ]);

        abort_if($this->editingPath === null, 404);

        $source = $this->directory()->resolve($this->editingPath);

        if (!$source || !is_file($source)) {
            $this->cancelEdit();
            $this->notice = 'File sudah dipindahkan atau dihapus dari server.';

            return;
        }

        $extension = pathinfo($this->editingPath, PATHINFO_EXTENSION);
        $filename = $this->safeBaseName($this->editingTitle);

        if ($extension !== '') {
            $filename .= '.' . $extension;
        }

        $parent = dirname($this->editingPath);
        $destination = $parent === '.'
            ? $filename
            : $parent . '/' . $filename;

        if ($destination === $this->editingPath) {
            $this->cancelEdit();

            return;
        }

        if ($this->directory()->resolve($destination) !== null) {
            $this->addError('editingTitle', 'Nama tersebut sudah digunakan.');

            return;
        }

        $moved = Storage::disk('materials')->move(
            $this->editingPath,
            $destination
        );

        if (!$moved) {
            $this->addError('editingTitle', 'File gagal diganti namanya.');

            return;
        }

        $this->cancelEdit();
        $this->notice = 'Nama file berhasil diperbarui.';
    }

    public function cancelEdit(): void
    {
        $this->reset('editingPath', 'editingTitle');
        $this->resetValidation('editingTitle');
    }

    public function delete(string $path): void
    {
        Gate::authorize('manage-lab');

        $absolute = $this->directory()->resolve($path);

        if ($absolute === null) {
            $this->notice = 'File sudah tidak tersedia.';

            return;
        }

        abort_unless(is_file($absolute), 403);

        if (!Storage::disk('materials')->delete($path)) {
            $this->addError('operation', 'File gagal dihapus.');

            return;
        }

        if ($this->editingPath === $path) {
            $this->cancelEdit();
        }

        $this->notice = 'File berhasil dihapus dari server.';
    }

    public function render()
    {
        Gate::authorize('view-materials');

        $absolute = $this->directory()->resolve($this->path);

        if (!$absolute || !is_dir($absolute)) {
            $this->path = '';
            $this->reset('search', 'file', 'title');
            $this->uploadIteration++;
            $this->cancelEdit();
            $this->notice = 'Folder berubah atau dihapus. Kembali ke folder utama.';
        }

        $items = $this->directory()->entries($this->path);
        $search = trim($this->search);

        if ($search !== '') {
            $items = array_values(array_filter(
                $items,
                fn(array $item) => mb_stripos($item['name'], $search) !== false
            ));
        }

        $parent = dirname($this->path);

        return view('livewire.materials.index', [
            'items' => $items,
            'parent' => $parent === '.' ? '' : $parent,
        ])->layout('components.layouts.app', [
            'title' => 'Materi Praktikum',
        ]);
    }
}
