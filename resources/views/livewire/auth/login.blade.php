<section class="auth-form">
    <p class="auth-form-eyebrow">Selamat datang kembali</p>

    <h2 class="mt-2 text-3xl font-bold">Masuk ke lab</h2>

    <p class="mt-3 text-sm leading-relaxed text-muted">
        Gunakan akun kamu untuk mengakses portal laboratorium.
    </p>

    <form wire:submit="login" class="mt-8 space-y-5">
        <div>
            <label for="login-email" class="form-label">Email</label>
            <input
                wire:model="email"
                id="login-email"
                type="email"
                autocomplete="username"
                maxlength="255"
                required
                class="form-input"
                placeholder="nama@email.com"
            >
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="login-password" class="form-label">Password</label>
            <input
                wire:model="password"
                id="login-password"
                type="password"
                autocomplete="current-password"
                maxlength="72"
                required
                class="form-input"
                placeholder="********"
            >
            @error('password') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="login"
            class="button-primary w-full"
        >
            <span wire:loading.remove wire:target="login">Masuk</span>
            <span wire:loading wire:target="login">Memproses...</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-muted">
        Belum punya akun?
        <a href="{{ route('register') }}" x-on:click.prevent="$dispatch('auth-switch', 'register')" class="text-secondary hover:underline">
            Daftar
        </a>
    </p>
</section>
