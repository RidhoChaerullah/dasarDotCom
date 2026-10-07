<section class="auth-form">
    <p class="auth-form-eyebrow">Mulai praktikum kamu</p>

    <h2 class="mt-2 text-3xl font-bold">Buat akun</h2>

    <p class="mt-3 text-sm leading-relaxed text-muted">
        Daftar menggunakan NIM dan email kamu.
    </p>

    <form wire:submit="register" class="mt-8 space-y-5">
        <div>
            <label for="register-name" class="form-label">Nama lengkap</label>
            <input
                wire:model="name"
                id="register-name"
                type="text"
                autocomplete="name"
                maxlength="100"
                required
                class="form-input"
                placeholder="Nama lengkap kamu"
            >
            @error('name') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="register-nim" class="form-label">NIM</label>
            <input
                wire:model="nim"
                id="register-nim"
                type="text"
                inputmode="numeric"
                maxlength="30"
                required
                class="form-input"
                placeholder="203031001"
            >
            @error('nim') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="register-email" class="form-label">Email</label>
            <input
                wire:model="email"
                id="register-email"
                type="email"
                autocomplete="email"
                maxlength="255"
                required
                class="form-input"
                placeholder="nama@email.com"
            >
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="register-password" class="form-label">Password</label>
            <input
                wire:model="password"
                id="register-password"
                type="password"
                autocomplete="new-password"
                minlength="8"
                maxlength="72"
                required
                class="form-input"
                placeholder="********"
            >
            <p class="mt-2 text-xs text-muted">Minimal 8 karakter.</p>
            @error('password') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="register-password_confirmation" class="form-label">
                Konfirmasi password
            </label>
            <input
                wire:model="password_confirmation"
                id="register-password_confirmation"
                type="password"
                autocomplete="new-password"
                minlength="8"
                maxlength="72"
                required
                class="form-input"
                placeholder="********"
            >
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="register"
            class="button-primary w-full"
        >
            <span wire:loading.remove wire:target="register">Buat akun</span>
            <span wire:loading wire:target="register">Memproses...</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-muted">
        Sudah punya akun?
        <a href="{{ route('login') }}" x-on:click.prevent="$dispatch('auth-switch', 'login')" class="text-secondary hover:underline">
            Masuk
        </a>
    </p>
</section>
