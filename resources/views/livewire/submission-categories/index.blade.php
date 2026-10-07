<section class="lab-admin admin-submission-categories">
    <div class="admin-heading">
        <a href="{{ route('management.index') }}" class="admin-back">
            ← Pengelolaan
        </a>

        <h1 class="admin-title">Kategori pengumpulan</h1>

        <p class="admin-description">
            Atur jenis pengumpulan untuk kebutuhan praktikum.
        </p>
    </div>

    @if ($notice !== '')

        <div role="status"
            class="admin-notice">
            {{ $notice }}
        </div>
    @endif

    @error('operation')
        <div role="alert"
            class="admin-alert">
            {{ $message }}
        </div>
    @enderror

    <div class="admin-category-layout">
        <section class="admin-editor">
            <h2 class="text-lg font-semibold">
                {{ $editingId === null ? 'Tambah kategori' : 'Edit kategori' }}
            </h2>

            <form wire:submit="save" class="mt-5 space-y-5">
                <div>
                    <label for="category-name" class="form-label">
                        Nama kategori
                    </label>

                    <input wire:model="name" id="category-name" type="text" maxlength="100"
                        placeholder="Contoh: Proyek Akhir" required class="form-input">

                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category-description" class="form-label">
                        Deskripsi
                    </label>

                    <textarea wire:model="description" id="category-description" rows="4" maxlength="1000"
                        placeholder="Keterangan singkat, boleh dikosongkan." class="form-input resize-y"></textarea>

                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="admin-toggle">
                        <input wire:model="isActive" type="checkbox" class="h-4 w-4 accent-primary">

                        <span class="text-sm text-ink">
                            Kategori aktif
                        </span>
                    </label>

                    @error('isActive')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="admin-form-actions">
                    <button type="submit" wire:loading.attr="disabled" wire:target="save" class="button-primary">
                        <span wire:loading.remove wire:target="save">
                            {{ $editingId === null ? 'Tambah' : 'Simpan perubahan' }}
                        </span>

                        <span wire:loading wire:target="save">
                            Menyimpan...
                        </span>
                    </button>

                    @if ($editingId !== null)
                        <button type="button" wire:click="cancelEdit"
                            class="admin-button admin-button-quiet">
                            Batal
                        </button>
                    @endif
                </div>
            </form>
        </section>

        <div class="admin-category-list">
            @forelse ($categories as $category)
                <article wire:key="category-{{ $category->id }}"
                    class="admin-card">
                    <div class="admin-category-row">
                        <div class="min-w-0">
                            <h2 class="break-words text-lg font-semibold">
                                {{ $category->name }}
                            </h2>

                            <p class="mt-2 break-words text-sm leading-relaxed text-muted">
                                {{ $category->description ?: 'Belum ada deskripsi.' }}
                            </p>
                        </div>
                        <div class="admin-category-actions">
                            <button type="button" wire:click="edit({{ $category->id }})" wire:loading.attr="disabled"
                                wire:target="edit,save,delete"
                                class="admin-button admin-button-quiet">
                                Edit
                            </button>

                            <button type="button" wire:click="delete({{ $category->id }})"
                                wire:confirm="Hapus kategori ini? Tindakan ini tidak bisa dibatalkan."
                                wire:loading.attr="disabled" wire:target="edit,save,delete"
                                class="admin-button admin-button-danger">
                                Hapus
                            </button>
                        </div>
                    </div>

                    <span @class([
                        'admin-badge mt-4',
                        'bg-emerald-400/10 text-success' => $category->is_active,
                        'bg-slate-400/10 text-muted' => !$category->is_active,
                    ])>
                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </article>
            @empty
                <div
                    class="admin-empty">
                    Belum ada kategori pengumpulan.
                </div>
            @endforelse
        </div>
    </div>
</section>
