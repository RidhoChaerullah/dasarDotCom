<section class="lab-admin admin-banners">
    <div class="admin-heading">
        <p class="admin-eyebrow">PENGELOLAAN LABORATORIUM</p>
        <h1 class="admin-title">Banner kegiatan</h1>
        <p class="admin-description">Bagikan webinar, pelatihan, dan kabar terbaru kepada warga lab.</p>
        <a href="{{ route('dashboard') }}" class="admin-back">Lihat dashboard →</a>
    </div>
    @if($notice)
        <p role="status" class="admin-notice">{{ $notice }}</p>
    @endif
    @error('operation')
        <p role="alert" class="admin-alert">{{ $message }}</p>
    @enderror
    <form wire:submit="save" class="admin-editor space-y-5" x-data="{ uploading: false }"
        x-on:livewire-upload-start="uploading = true" x-on:livewire-upload-finish="uploading = false"
        x-on:livewire-upload-error="uploading = false" x-on:livewire-upload-cancel="uploading = false">
        <h2 class="text-xl font-semibold">{{ $editingId ? 'Edit banner' : 'Tambah banner' }}</h2>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="banner-title" class="form-label">Judul kegiatan</label>
                <input id="banner-title" wire:model="title" maxlength="150" required class="form-input">
                @error('title')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="banner-link" class="form-label">Tautan pendaftaran (opsional)</label>
                <input id="banner-link" wire:model="link_url" type="url" maxlength="2048" placeholder="https://…" class="form-input">
                @error('link_url')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="banner-image" class="form-label">Gambar kegiatan</label>
                <input id="banner-image" wire:key="banner-upload-{{ $uploadIteration }}" wire:model="image"
                    type="file" accept="image/jpeg,image/png,image/webp" class="form-input">
                <p class="mt-2 text-xs text-muted">JPG, PNG, atau WebP; maksimal 5 MB. Poster ditampilkan utuh. Saat edit, kosongkan jika gambar tetap.</p>
                <p x-show="uploading" x-cloak class="mt-2 text-sm text-secondary">Mengunggah gambar…</p>
                @error('image')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="banner-order" class="form-label">Urutan tampil</label>
                <input id="banner-order" wire:model="sort_order" type="number" min="0" max="99999" required class="form-input">
                <p class="mt-2 text-xs text-muted">Angka lebih kecil tampil lebih dulu.</p>
                @error('sort_order')
                    <p class="form-error">{{ $message }}</p>
                @enderror
                <label class="admin-toggle mt-4"><input wire:model="is_active" type="checkbox"> Tampilkan di dashboard</label>
                @error('is_active')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="admin-form-actions">
            <button class="button-primary" type="submit" :disabled="uploading" wire:loading.attr="disabled" wire:target="save,image">Simpan banner</button>
            @if($editingId)
                <button type="button" wire:click="cancel" class="admin-button admin-button-quiet">Batal edit</button>
            @endif
        </div>
    </form>
    <h2 class="admin-section-title">Daftar banner</h2>

    <div class="admin-banner-grid">
        @forelse($banners as $banner)
            <article wire:key="banner-{{ $banner->id }}" class="admin-banner-card">
                <img src="{{ route('banners.image', $banner) }}" alt="{{ $banner->title }}" class="admin-banner-image" loading="lazy">
                <div class="admin-banner-content">
                    <p class="text-xs text-muted">Urutan {{ $banner->sort_order }} · {{ $banner->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                    <h2 class="break-words font-semibold">{{ $banner->title }}</h2>
                    @if($banner->link_url)
                        <a href="{{ $banner->link_url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-secondary">Lihat tautan ↗</a>
                    @endif
                    <div class="admin-banner-actions">
                        <button type="button" wire:click="edit({{ $banner->id }})" class="admin-button admin-button-quiet">Edit</button>
                        <button type="button" wire:click="toggle({{ $banner->id }})" class="admin-button admin-button-quiet">{{ $banner->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        <button type="button" wire:click="delete({{ $banner->id }})" wire:confirm="Hapus banner dan gambarnya?" class="admin-button admin-button-danger">Hapus</button>
                    </div>
                </div>
            </article>
        @empty
            <p class="admin-empty">Belum ada banner. Tambahkan kegiatan pertama di atas.</p>
        @endforelse
    </div>
    <div class="admin-pagination">
        {{ $banners->links() }}
    </div>
</section>
