<?php
namespace App\Livewire\Banners;
use App\Models\Banner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;
class Index extends Component
{
    use WithFileUploads, WithPagination;
    #[Locked] public ?int $editingId = null;
    #[Locked] public int $uploadIteration = 0;
    public string $title = '';
    public string $link_url = '';
    public $sort_order = 0;
    public bool $is_active = true;
    public $image;
    public string $notice = '';

    private function authorizeManager(): void
    {
        abort_unless(auth()->user()?->is_active, 403);
        Gate::authorize('manage-lab');
    }
    public function edit(int $id): void
    {
        $this->authorizeManager();
        $banner = Banner::findOrFail($id);
        $this->resetValidation();
        $this->reset('image', 'notice');
        $this->uploadIteration++;
        $this->editingId = $banner->id;
        $this->title = $banner->title;
        $this->link_url = $banner->link_url ?? '';
        $this->sort_order = $banner->sort_order;
        $this->is_active = $banner->is_active;
    }
    public function cancel(): void
    {
        $this->authorizeManager();
        $this->reset('editingId', 'title', 'link_url', 'sort_order', 'is_active', 'image');
        $this->uploadIteration++;
        $this->resetValidation();
    }
    public function save(): void
    {
        $this->authorizeManager();
        $this->notice = '';
        $this->title = trim($this->title);
        $this->link_url = trim($this->link_url);
        $this->validate([
            'title' => ['required', 'string', 'max:150'],
            'link_url' => ['nullable', 'url:http,https', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:99999'],
            'is_active' => ['boolean'],
            'image' => [$this->editingId ? 'nullable' : 'required', 'image',
                'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120',
                'dimensions:max_width=8000,max_height=8000'],
        ], [
            'image.required' => 'Pilih gambar kegiatan terlebih dahulu.',
            'image.max' => 'Ukuran gambar maksimal 5 MB.',
            'link_url.url' => 'Gunakan tautan lengkap berawalan http:// atau https://.',
        ]);
        $newPath = null;
        $oldPath = null;
        try {
            if ($this->image) {
                $newPath = $this->image->store('images', 'banners');
                if (! $newPath) { throw new \RuntimeException('Banner upload failed.'); }
            }
            DB::transaction(function () use ($newPath, &$oldPath) {
                $banner = $this->editingId
                    ? Banner::query()->lockForUpdate()->findOrFail($this->editingId)
                    : new Banner;
                if ($newPath) {
                    $oldPath = $banner->image_path;
                    $banner->image_path = $newPath;
                }
                $banner->title = $this->title;
                $banner->link_url = $this->link_url ?: null;
                $banner->sort_order = $this->sort_order;
                $banner->is_active = $this->is_active;
                $banner->save();
            });
        } catch (Throwable $e) {
            $this->removeImage($newPath);
            report($e);
            $this->addError('operation', 'Banner gagal disimpan. Muat ulang halaman lalu coba lagi.');
            return;
        }
        $this->removeImage($oldPath);
        $this->cancel();
        $this->resetPage();
        $this->notice = 'Banner berhasil disimpan.';
    }
    public function toggle(int $id): void
    {
        $this->authorizeManager();
        DB::transaction(function () use ($id) {
            $banner = Banner::query()->lockForUpdate()->findOrFail($id);
            $banner->is_active = ! $banner->is_active;
            $banner->save();
        });
        if ($this->editingId === $id) { $this->cancel(); }
        $this->notice = 'Status banner diperbarui.';
    }
    public function delete(int $id): void
    {
        $this->authorizeManager();
        $path = DB::transaction(function () use ($id) {
            $banner = Banner::query()->lockForUpdate()->findOrFail($id);
            $path = $banner->image_path;
            $banner->delete();
            return $path;
        });
        $this->removeImage($path);
        if ($this->editingId === $id) { $this->cancel(); }
        $this->resetPage();
        $this->notice = 'Banner berhasil dihapus.';
    }
    private function removeImage(?string $path): void
    {
        if (! $path) { return; }
        try { Storage::disk('banners')->delete($path); }
        catch (Throwable $e) { report($e); }
    }
    public function render()
    {
        $this->authorizeManager();
        return view('livewire.banners.index', [
            'banners' => Banner::orderBy('sort_order')->orderByDesc('id')->paginate(12),
        ])->layout('components.layouts.app', ['title' => 'Banner Kegiatan']);
    }
}
