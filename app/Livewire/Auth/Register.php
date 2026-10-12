<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use App\Livewire\Auth\Concerns\TransitionsAfterAuthentication;

class Register extends Component
{
    use TransitionsAfterAuthentication;

    public string $name = '';
    public string $nim = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $key = 'register:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        RateLimiter::hit($key, 60);

        $this->name = trim($this->name);
        $this->nim = trim($this->nim);
        $this->email = mb_strtolower(trim($this->email));

        $data = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'nim' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/', 'unique:users,nim'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'max:72', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.regex' => 'NIM hanya boleh berisi angka.',
            'nim.unique' => 'NIM sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'nim' => $data['nim'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);
        request()->session()->regenerate();

        $this->reset('password', 'password_confirmation');

        return $this->transitionAfterAuthentication(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('components.layouts.app', ['title' => 'Daftar']);
    }
}