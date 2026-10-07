<section class="lab-admin admin-users">
    <div class="admin-heading">
        <a href="{{ route('management.index') }}" class="admin-back">
            ← Pengelolaan
        </a>

        <h1 class="admin-title">Pengguna laboratorium</h1>

        <p class="admin-description">
            Kelola role dan akses akun pengguna.
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

    @if ($editingId !== null)
        <section class="admin-editor">
            <h2 class="text-xl font-semibold">
                Edit akses: {{ $editingName }}
            </h2>

            <form wire:submit="save"
                wire:confirm="Simpan perubahan pengguna? Perubahan role atau status akan mengakhiri sesi login pengguna tersebut."
                class="mt-6 space-y-5">
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="user-role" class="form-label">Role</label>

                        <select wire:model="role" id="user-role" required class="form-input">
                            <option value="mahasiswa">Mahasiswa</option>
                            <option value="asisten">Asisten</option>
                            <option value="admin">Admin</option>
                        </select>

                        @error('role')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="user-nim" class="form-label">NIM</label>

                        <input wire:model="nim" id="user-nim" type="text" inputmode="numeric" maxlength="30"
                            class="form-input">

                        <p class="mt-2 text-xs text-muted">
                            Wajib untuk mahasiswa.
                        </p>

                        @error('nim')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <label class="admin-toggle">
                    <input wire:model="isActive" type="checkbox" class="h-4 w-4 accent-primary">

                    <span class="text-sm">Akun aktif</span>
                </label>

                @error('isActive')
                    <p class="form-error">{{ $message }}</p>
                @enderror

                <p class="text-sm text-muted">
                    Akun nonaktif tidak dapat masuk. Riwayat pengumpulan tetap tersimpan.
                </p>

                <div class="admin-form-actions">
                    <button type="submit" wire:loading.attr="disabled" wire:target="save" class="button-primary">
                        <span wire:loading.remove wire:target="save">
                            Simpan perubahan
                        </span>
                        <span wire:loading wire:target="save">
                            Menyimpan...
                        </span>
                    </button>

                    <button type="button" wire:click="cancelEdit" class="admin-button admin-button-quiet">
                        Batal
                    </button>
                </div>
            </form>
        </section>
    @endif

    <div class="admin-user-filters">
        <div class="admin-user-search">
            <label for="user-search" class="form-label">Cari pengguna</label>

            <input wire:model.live.debounce.300ms="search" id="user-search" type="search" maxlength="150"
                placeholder="Nama, NIM, atau email..." class="form-input">
        </div>

        <div>
            <label for="role-filter" class="form-label">Role</label>

            <select wire:model.live="roleFilter" id="role-filter" class="form-input">
                <option value="">Semua role</option>
                <option value="admin">Admin</option>
                <option value="asisten">Asisten</option>
                <option value="mahasiswa">Mahasiswa</option>
            </select>
        </div>

        <div>
            <label for="status-filter" class="form-label">Status</label>

            <select wire:model.live="statusFilter" id="status-filter" class="form-input">
                <option value="">Semua status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
            </select>
        </div>
    </div>

    <p class="admin-result-count">
        {{ $users->total() }} pengguna ditemukan
    </p>

    <div class="admin-user-list">
        @forelse ($users as $user)
            <article wire:key="user-{{ $user->id }}" class="admin-card">
                <div class="admin-user-row">
                    <div class="min-w-0">
                        <h2 class="break-words text-lg font-semibold">
                            {{ $user->name }}

                            @if ($user->id === auth()->id())
                                <span class="text-sm font-normal text-secondary">
                                    (Kamu)
                                </span>
                            @endif
                        </h2>

                        <p class="mt-2 break-all text-sm text-muted">
                            {{ $user->email }}
                        </p>

                        <p class="mt-1 text-sm text-muted">
                            NIM: {{ $user->nim ?? '—' }}
                        </p>

                        <p class="mt-2 text-sm text-muted">
                            Nomor HP:
                            <span class="text-ink">
                                {{ $user->no_hp_mhs ?: 'Belum diisi' }}
                            </span>
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span
                                class="admin-badge admin-badge-role">
                                {{ ucfirst($user->role) }}
                            </span>

                            <span @class([
                                'admin-badge',
                                'bg-emerald-400/10 text-success' => $user->is_active,
                                'bg-red-400/10 text-danger' => !$user->is_active,
                            ])>
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <p class="mt-3 text-xs text-muted">
                            Terdaftar
                            {{ $user->created_at->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                            WIB
                        </p>
                    </div>

                    @if ($user->id !== auth()->id())
                        <div class="shrink-0">
                            <button type="button" wire:click="edit({{ $user->id }})" wire:loading.attr="disabled"
                                wire:target="edit,save" class="button-primary">
                                Edit akses
                            </button>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div
                class="admin-empty">
                Tidak ada pengguna yang cocok.
            </div>
        @endforelse
    </div>

    <div class="admin-pagination">
        {{ $users->links() }}
    </div>
</section>
