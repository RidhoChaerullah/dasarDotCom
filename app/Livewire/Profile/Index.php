<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Index extends Component
{
    public string $name = '';
    public string $email = '';
    public string $no_hp_mhs = '';

    public string $profile_password = '';

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public string $notice = '';

    private function activeUser(): User
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->is_active, 403);

        return $user;
    }

    public function mount(): void
    {
        $user = $this->activeUser();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->no_hp_mhs = $user->no_hp_mhs ?? '';
    }

    public function saveProfile(): void
    {
        $user = $this->activeUser();

        $this->notice = '';
        $this->resetValidation();

        $this->name = Str::squish($this->name);
        $this->email = Str::lower(trim($this->email));

        // Hilangkan spasi, tanda kurung, dan tanda hubung.
        $this->no_hp_mhs = preg_replace(
            '/[\s()\-]+/',
            '',
            trim($this->no_hp_mhs)
        ) ?? '';

        $emailChanged = $this->email !== $user->email;

        try {
            $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($user->id),
                ],
                'no_hp_mhs' => [
                    'nullable',
                    'string',
                    'regex:/^\+?[0-9]{8,15}$/',
                ],
                'profile_password' => $emailChanged
                    ? ['required', 'string', 'current_password:web']
                    : ['nullable', 'string'],
            ], [
                'name.required' => 'Nama wajib diisi.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email belum benar.',
                'email.unique' => 'Email sudah digunakan akun lain.',
                'no_hp_mhs.regex' =>
                'Isi nomor HP dengan 8–15 digit, misalnya 081234567890 atau +6281234567890.',
                'profile_password.required' =>
                'Masukkan password saat ini untuk mengubah email.',
                'profile_password.current_password' =>
                'Password saat ini belum sesuai.',
            ]);
        } finally {
            $this->reset('profile_password');
        }

        // Identitas akun berasal dari sesi login, bukan input pengguna.
        $user->name = $this->name;
        $user->email = $this->email;
        $user->no_hp_mhs = $this->no_hp_mhs !== ''
            ? $this->no_hp_mhs
            : null;

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->notice = 'Profil berhasil diperbarui.';
    }

    public function updatePassword(): void
    {
        $user = $this->activeUser();

        $this->notice = '';
        $this->resetValidation();

        try {
            $this->validate([
                'current_password' => [
                    'required',
                    'string',
                    'current_password:web',
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'max:72',
                    'confirmed',
                    'different:current_password',
                ],
            ], [
                'current_password.required' =>
                'Masukkan password saat ini.',
                'current_password.current_password' =>
                'Password saat ini belum sesuai.',
                'password.required' =>
                'Password baru wajib diisi.',
                'password.min' =>
                'Password baru minimal 8 karakter.',
                'password.max' =>
                'Password baru maksimal 72 karakter.',
                'password.confirmed' =>
                'Konfirmasi password belum sama.',
                'password.different' =>
                'Password baru harus berbeda dari password saat ini.',
            ]);

            DB::transaction(function () use ($user) {
                $user->password = Hash::make($this->password);
                $user->remember_token = Str::random(60);
                $user->save();

                // Proyek kita menggunakan SESSION_DRIVER=database.
                DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->delete();
            });
        } finally {
            $this->reset(
                'current_password',
                'password',
                'password_confirmation'
            );
        }

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->redirectRoute('login');
    }

    public function render()
    {
        return view('livewire.profile.index', [
            'account' => $this->activeUser(),
        ])->layout('components.layouts.app', [
            'title' => 'Profil Akun',
        ]);
    }
}
