<section class="lab-auth" data-mode="{{ $initialMode }}" x-data="{
    mode: @js($initialMode),
    loginUrl: @js(route('login')),
    registerUrl: @js(route('register')),

    switchTo(next, push = true) {
        if (!['login', 'register'].includes(next) || next === this.mode) return;
        this.mode = next;

        document.title =
            (next === 'register' ? 'Daftar' : 'Masuk') + ' — dasar.com';

        if (push) {
            history.pushState({},
                '',
                next === 'register' ? this.registerUrl : this.loginUrl
            );
        }
    }
}" x-bind:data-mode="mode"
    x-on:auth-switch.window="switchTo($event.detail)"
    x-on:popstate.window="
        switchTo(
            location.pathname === new URL(registerUrl, location.origin).pathname
                ? 'register'
                : 'login',
            false
        )
    ">
    <aside class="lab-auth-copy">
        <div class="lab-auth-copy-login" x-bind:inert="mode !== 'login'" x-bind:aria-hidden="mode !== 'login'" x-cloak>
            <p class="lab-auth-caption">PORTAL LABORATORIUM</p>

            <h1>Selamat datang<br>kembali.</h1>

            <p class="lab-auth-description">
                Masuk untuk mengakses tugas, jadwal,
                dan kegiatan laboratorium.
            </p>

            <button type="button" class="lab-auth-switch" x-on:click="switchTo('register')">
                Belum punya akun? Daftar →
            </button>
        </div>

        <div class="lab-auth-copy-register" x-bind:inert="mode !== 'register'" x-bind:aria-hidden="mode !== 'register'" x-cloak>
            <p class="lab-auth-caption">MULAI LANGKAHMU</p>

            <h1>Siap belajar<br>bersama?</h1>

            <p class="lab-auth-description">
                Daftarkan akunmu untuk mengikuti praktikum
                dan mengumpulkan tugas.
            </p>

            <button type="button" class="lab-auth-switch" x-on:click="switchTo('login')">
                Sudah punya akun? Masuk ←
            </button>
        </div>
    </aside>

    <div class="lab-auth-panel">
        <div class="lab-auth-login" x-bind:inert="mode !== 'login'"
            x-bind:aria-hidden="mode !== 'login'" x-cloak>
            <livewire:auth.login />
        </div>

        <div class="lab-auth-register" x-bind:inert="mode !== 'register'"
            x-bind:aria-hidden="mode !== 'register'" x-cloak>
            <livewire:auth.register />
        </div>
    </div>
</section>
