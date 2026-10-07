<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';

    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $this->email = mb_strtolower(trim($this->email));

        $this->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:72'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $key = 'login:' . hash('sha256', $this->email . '|' . request()->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (! Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
            'is_active' => true,
        ])) {
            RateLimiter::hit($key, 60);

            $this->reset('password');

            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak cocok, atau akun sedang nonaktif.',
            ]);
        }

        RateLimiter::clear($key);
        request()->session()->regenerate();

        $this->reset('password');

        return redirect()->intended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.app', ['title' => 'Masuk']);
    }
}
