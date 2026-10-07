<section class="lab-profile">
    <div class="profile-heading">
        <p class="profile-eyebrow">
            AKUN SAYA
        </p>

        <h1 class="profile-title">Profil akun</h1>

        <p class="profile-description">
            Perbarui informasi kontak dan password akunmu.
        </p>

        <dl class="profile-account-meta">
            <div>
                <dt class="text-sm text-muted">NIM</dt>
                <dd class="mt-1 font-medium">
                    {{ $account->nim ?? '—' }}
                </dd>
            </div>

            <div>
                <dt class="text-sm text-muted">Role</dt>
                <dd class="mt-1 font-medium">
                    {{ ucfirst($account->role) }}
                </dd>
            </div>
        </dl>

        <p class="profile-account-note">
            Hubungi Admin jika NIM perlu diperbaiki.
        </p>
    </div>

    @if ($notice)
        <div role="status"
            class="profile-notice">
            {{ $notice }}
        </div>
    @endif

    <div class="profile-forms">
        <form wire:submit="saveProfile"
            class="profile-form">
            <div class="profile-form-heading">
                <span class="profile-step" aria-hidden="true">01</span>
                <div>
                    <h2>Informasi pribadi</h2>
                    <p>Pastikan informasi kontakmu selalu terbaru.</p>
                </div>
            </div>

            <div>
                <label for="profile-name" class="form-label">Nama</label>
                <input id="profile-name" type="text" wire:model="name" autocomplete="name" maxlength="255" required
                    class="form-input">
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="profile-email" class="form-label">Email</label>
                <input id="profile-email" type="email" wire:model="email" autocomplete="email" maxlength="255"
                    required class="form-input">
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="profile-phone" class="form-label">
                    Nomor HP yang bisa dihubungi
                </label>

                <input id="profile-phone" type="tel" wire:model="no_hp_mhs" autocomplete="tel" maxlength="20"
                    placeholder="081234567890" class="form-input">

                <p class="mt-2 text-xs text-muted">
                    Opsional. Bisa menggunakan awalan 08 atau +62.
                </p>

                @error('no_hp_mhs')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="profile-password" class="form-label">
                    Password saat ini
                </label>
                <input id="profile-password" type="password" wire:model="profile_password"
                    autocomplete="current-password" class="form-input">
                <p class="mt-2 text-xs text-muted">
                    Isi hanya jika kamu mengubah email.
                </p>
                @error('profile_password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="profile-form-actions">
                <button type="submit" wire:loading.attr="disabled" wire:target="saveProfile" class="button-primary">
                    <span wire:loading.remove wire:target="saveProfile">Simpan profil</span>
                    <span wire:loading wire:target="saveProfile" role="status">Menyimpan profil…</span>
                </button>
            </div>
        </form>

        <form wire:submit="updatePassword"
            class="profile-form">
            <div class="profile-form-heading">
                <span class="profile-step" aria-hidden="true">02</span>
                <div>
                    <h2>Keamanan akun</h2>
                    <p>Ganti password untuk memperbarui akses akunmu.</p>
                </div>
            </div>

            <p class="profile-security-note">
                Setelah password diganti, kamu akan keluar dari semua
                perangkat dan perlu masuk kembali.
            </p>

            <div>
                <label for="current-password" class="form-label">
                    Password saat ini
                </label>
                <input id="current-password" type="password" wire:model="current_password"
                    autocomplete="current-password" required class="form-input">
                @error('current_password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="new-password" class="form-label">
                    Password baru
                </label>
                <input id="new-password" type="password" wire:model="password" autocomplete="new-password"
                    minlength="8" maxlength="72" required class="form-input">
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password-confirmation" class="form-label">
                    Konfirmasi password baru
                </label>
                <input id="password-confirmation" type="password" wire:model="password_confirmation"
                    autocomplete="new-password" minlength="8" maxlength="72" required class="form-input">
            </div>

            <div class="profile-form-actions">
                <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword" class="button-primary">
                    <span wire:loading.remove wire:target="updatePassword">Simpan dan masuk ulang</span>
                    <span wire:loading wire:target="updatePassword" role="status">Memperbarui password…</span>
                </button>
            </div>
        </form>
    </div>
</section>
