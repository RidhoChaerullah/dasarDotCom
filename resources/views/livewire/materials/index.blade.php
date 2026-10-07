<section wire:poll.5s class="lab-materials" x-data="{ uploadOpen: false }">
    <header class="mat-header">
        <div>
            <p class="mat-eyebrow">RUANG BELAJAR</p>
            <h1>Materi praktikum<span>.</span></h1>
            <p class="mat-subtitle">Temukan bahan belajar, buka folder, dan unduh materi yang kamu butuhkan.</p>
        </div>
        @can('manage-lab')
            <button type="button" class="mat-button mat-button-primary"
                aria-controls="material-upload-panel" x-bind:aria-expanded="uploadOpen"
                x-on:click="uploadOpen = !uploadOpen">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4m-5 5 5-5 5 5M5 16v4h14v-4"/></svg> <span>Tambah materi</span>
            </button>
        @endcan
    </header>

    @if($notice !== '')
        <div role="status" class="mat-notice"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg> <p>{{ $notice }}</p></div>
    @endif
    @error('operation')
        <div role="alert" class="mat-alert"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></svg> <p>{{ $message }}</p></div>
    @enderror

    @can('manage-lab')
        <section id="material-upload-panel" class="mat-upload" x-show="uploadOpen" x-cloak aria-labelledby="material-upload-title">
            <div class="mat-upload-heading">
                <div>
                    <h2 id="material-upload-title">Tambah bahan belajar</h2>
                    <p>File disimpan ke folder yang sedang kamu buka.</p>
                </div>
                <button type="button" class="mat-icon-button" aria-label="Tutup formulir unggah" x-on:click="uploadOpen = false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 6 12 12M6 18 18 6"/></svg></button>
            </div>
            <form wire:submit="save" class="mat-upload-form">
                <div>
                    <label for="material-title" class="form-label">Nama materi</label>
                    <input wire:model="title" id="material-title" type="text" maxlength="150"
                        placeholder="Contoh: Pertemuan 1" required class="form-input">
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="material-file" class="form-label">File materi</label>
                    <input wire:model="file" wire:key="upload-{{ $uploadIteration }}" id="material-file"
                        type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.7z,.txt"
                        required class="form-input mat-file-input" aria-describedby="material-file-help">
                    <p id="material-file-help" class="mat-help">PDF, Word, PowerPoint, Excel, ZIP, RAR, 7Z, atau TXT. Maksimal 10 MB.</p>
                    <p wire:loading wire:target="file" class="mat-upload-status" role="status">Menyiapkan file…</p>
                    @error('file')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="mat-upload-footer">
                    <span class="mat-upload-location">Tujuan: {{ $path !== '' ? $path : 'Folder utama' }}</span>
                    <button type="submit" wire:loading.attr="disabled" wire:target="file,save" class="mat-button mat-button-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4m-5 5 5-5 5 5M5 16v4h14v-4"/></svg> <span wire:loading.remove wire:target="save">Simpan materi</span>
                        <span wire:loading wire:target="save">Menyimpan…</span>
                    </button>
                </div>
            </form>
        </section>
    @endcan

    <div class="mat-location-bar">
        <nav class="mat-breadcrumb" aria-label="Lokasi folder materi">
            <span class="mat-breadcrumb-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/></svg></span>
            @if($path !== '')
                <button type="button" wire:click="openFolder('')">Materi</button>
                <span class="mat-breadcrumb-separator" aria-hidden="true">/</span>
                <span class="mat-current-path" aria-current="location">{{ str_replace('/', ' / ', $path) }}</span>
            @else
                <span class="mat-current-root" aria-current="location">Semua materi</span>
            @endif
        </nav>
        @if($path !== '')
            <button type="button" wire:click="openFolder({{ \Illuminate\Support\Js::from($parent) }})"
                class="mat-button mat-button-quiet"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m10 6-6 6 6 6M4 12h16"/></svg> <span>Kembali</span></button>
        @endif
    </div>

    <div class="mat-toolbar">
        <div class="mat-list-title">
            <h2>Isi folder</h2>
            <span>{{ count($items) }} item{{ trim($search) !== '' ? ' ditemukan' : '' }}</span>
        </div>
        <div class="mat-search">
            <label for="material-search" class="mat-visually-hidden">Cari di folder ini</label>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
            <input wire:model.live.debounce.300ms="search" id="material-search" type="search"
                maxlength="150" placeholder="Cari di folder ini…">
        </div>
    </div>

    <div class="mat-entry-list" aria-label="Folder dan file materi">
        @forelse($items as $item)
            <article wire:key="entry-{{ md5($item['path']) }}" class="mat-entry">
                @if($item['directory'])
                    <button type="button" wire:click="openFolder({{ \Illuminate\Support\Js::from($item['path']) }})"
                        class="mat-folder-row">
                        <span class="mat-entry-icon is-folder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/></svg></span>
                        <span class="mat-entry-copy">
                            <span class="mat-entry-name">{{ $item['name'] }}</span>
                            <span class="mat-entry-meta">Folder materi</span>
                        </span>
                        <span class="mat-open-folder">Buka folder <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg></span>
                    </button>
                @elseif($editingPath === $item['path'])
                    @can('manage-lab')
                        <form wire:submit="updateTitle" class="mat-edit-form">
                            <span class="mat-entry-icon is-file"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15Z"/></svg></span>
                            <div class="mat-edit-field">
                                <label for="editing-title" class="form-label">Nama file tanpa ekstensi</label>
                                <input wire:model="editingTitle" id="editing-title" type="text" maxlength="150" required class="form-input">
                                @error('editingTitle')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="mat-edit-actions">
                                <button type="submit" wire:loading.attr="disabled" wire:target="updateTitle" class="mat-button mat-button-primary">Simpan</button>
                                <button type="button" wire:click="cancelEdit" class="mat-button mat-button-quiet">Batal</button>
                            </div>
                        </form>
                    @endcan
                @else
                    <div class="mat-file-row">
                        <span class="mat-entry-icon is-file"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M14 3v6h6M8 13h8M8 17h5"/></svg></span>
                        <div class="mat-entry-copy">
                            <h3 class="mat-entry-name">{{ $item['name'] }}</h3>
                            <p class="mat-entry-meta">
                                <span class="mat-file-type">{{ strtoupper(pathinfo($item['name'], PATHINFO_EXTENSION)) ?: 'FILE' }}</span>
                                <span aria-hidden="true">·</span>
                                <span>{{ number_format($item['size'] / 1048576, 2) }} MB</span>
                            </p>
                        </div>
                        <div class="mat-file-actions">
                            <a href="{{ route('materials.download', ['path' => $item['path']]) }}" class="mat-download"
                                aria-label="Unduh {{ $item['name'] }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M5 16v4h14v-4"/></svg> <span>Unduh</span></a>
                            @can('manage-lab')
                                <span class="mat-action-divider" aria-hidden="true"></span>
                                <button type="button" wire:click="edit({{ \Illuminate\Support\Js::from($item['path']) }})"
                                    class="mat-icon-button" aria-label="Ubah nama {{ $item['name'] }}" title="Ubah nama"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15Z"/></svg></button>
                                <button type="button" wire:click="delete({{ \Illuminate\Support\Js::from($item['path']) }})"
                                    wire:confirm="Hapus file ini dari komputer server? Tindakan ini tidak bisa dibatalkan."
                                    wire:loading.attr="disabled" wire:target="delete" class="mat-icon-button is-danger"
                                    aria-label="Hapus {{ $item['name'] }}" title="Hapus file"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7M14 10v7"/></svg></button>
                            @endcan
                        </div>
                    </div>
                @endif
            </article>
        @empty
            <div class="mat-empty">
                <span class="mat-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></span>
                <h2>{{ trim($search) !== '' ? 'Belum ketemu yang kamu cari.' : 'Folder ini masih kosong.' }}</h2>
                <p>{{ trim($search) !== '' ? 'Coba kata kunci lain untuk nama file atau folder di lokasi ini.' : 'Materi akan muncul di sini saat file ditambahkan ke folder ini.' }}</p>
            </div>
        @endforelse
    </div>

    <p class="mat-footnote"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></svg> <span>Materi dapat diunduh tanpa login. Daftar mengikuti isi folder di server dan diperbarui setiap 5 detik.</span></p>
</section>
