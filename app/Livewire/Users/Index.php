<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Locked]
    public ?int $editingId = null;

    #[Locked]
    public string $editingName = '';

    public string $nim = '';
    public string $role = 'mahasiswa';
    public bool $isActive = true;

    public string $search = '';
    public string $roleFilter = '';
    public string $statusFilter = '';
    public string $notice = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        Gate::authorize('manage-users');

        $this->notice = '';
        $this->resetValidation();

        if ($id === (int) auth()->id()) {
            $this->addError(
                'operation',
                'Akun yang sedang kamu gunakan tidak dapat diubah dari halaman ini.'
            );

            return;
        }

        $user = User::findOrFail($id);

        $this->editingId = $user->id;
        $this->editingName = $user->name;
        $this->nim = $user->nim ?? '';
        $this->role = $user->role;
        $this->isActive = $user->is_active;
    }

    public function save(): void
    {
        Gate::authorize('manage-users');

        $this->notice = '';
        $this->resetValidation();

        abort_if($this->editingId === null, 404);

        $this->nim = trim($this->nim);

        DB::transaction(function () {
            // Urutkan penguncian agar perubahan oleh beberapa Admin
            // tetap memeriksa kondisi terbaru.
            $admins = User::query()
                ->where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $actor = $admins->firstWhere('id', (int) auth()->id());

            abort_unless($actor && $actor->is_active, 403);

            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($this->editingId);

            if ($user->id === $actor->id) {
                throw ValidationException::withMessages([
                    'operation' => 'Kamu tidak dapat mengubah akun sendiri dari halaman ini.',
                ]);
            }

            $this->validate([
                'role' => [
                    'required',
                    Rule::in(['admin', 'asisten', 'mahasiswa']),
                ],
                'isActive' => ['boolean'],
                'nim' => [
                    Rule::requiredIf(
                        $this->role === 'mahasiswa' || $user->nim !== null
                    ),
                    'nullable',
                    'string',
                    'max:30',
                    'regex:/^[0-9]+$/',
                    Rule::unique('users', 'nim')->ignore($user),
                ],
            ], [
                'role.in' => 'Role tidak valid.',
                'nim.required' => 'NIM wajib diisi untuk mahasiswa atau akun yang sudah memiliki NIM.',
                'nim.regex' => 'NIM hanya boleh berisi angka.',
                'nim.max' => 'NIM maksimal 30 karakter.',
                'nim.unique' => 'NIM sudah digunakan akun lain.',
            ]);

            $removingActiveAdmin = $user->role === 'admin'
                && $user->is_active
                && ($this->role !== 'admin' || !$this->isActive);

            if (
                $removingActiveAdmin
                && $admins->where('is_active', true)->count() <= 1
            ) {
                throw ValidationException::withMessages([
                    'operation' => 'Sistem harus memiliki minimal satu Admin aktif.',
                ]);
            }

            $accessChanged = $user->role !== $this->role
                || $user->is_active !== $this->isActive;

            $user->nim = $this->nim !== '' ? $this->nim : null;
            $user->role = $this->role;
            $user->is_active = $this->isActive;

            if ($accessChanged) {
                $user->setRememberToken(Str::random(60));
            }

            $user->save();

            if ($accessChanged) {
                // Project ini menggunakan SESSION_DRIVER=database.
                DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->delete();
            }
        }, 3);

        $this->cancelEdit();
        $this->notice = 'Data pengguna berhasil diperbarui.';
    }

    public function cancelEdit(): void
    {
        $this->reset(
            'editingId',
            'editingName',
            'nim',
            'role',
            'isActive'
        );

        $this->resetValidation();
    }

    public function render()
    {
        Gate::authorize('manage-users');

        $search = mb_substr(trim($this->search), 0, 150);

        $users = User::query()
            ->select([
                'id',
                'name',
                'nim',
                'email',
                'role',
                'is_active',
                'created_at',
                'no_hp_mhs',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(
                $this->roleFilter !== '',
                fn($query) => $query->where('role', $this->roleFilter)
            )
            ->when(
                in_array($this->statusFilter, ['active', 'inactive'], true),
                fn($query) => $query->where(
                    'is_active',
                    $this->statusFilter === 'active'
                )
            )
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.users.index', [
            'users' => $users,
        ])->layout('components.layouts.app', [
            'title' => 'Pengelolaan Pengguna',
        ]);
    }
}
