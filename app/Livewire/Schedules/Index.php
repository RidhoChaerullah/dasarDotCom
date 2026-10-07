<?php

namespace App\Livewire\Schedules;

use App\Models\Schedule;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Index extends Component
{
    #[Locked]
    public ?int $editingId = null;

    public bool $showForm = false;

    public string $course_name = '';
    public string $class_name = '';
    public string $semester = '';
    public string $room = '';
    public string $instructor = '';
    public string $day_of_week = '1';
    public string $starts_at = '08:00';
    public string $ends_at = '10:00';
    public bool $is_active = true;

    public string $search = '';
    public string $dayFilter = '';
    public string $notice = '';

    public function create(): void
    {
        Gate::authorize('manage-lab');

        $this->cancelEdit();
        $this->showForm = true;
        $this->notice = '';
    }

    public function edit(int $id): void
    {
        Gate::authorize('manage-lab');

        $schedule = Schedule::findOrFail($id);

        $this->editingId = $schedule->id;

        foreach (
            [
                'course_name',
                'class_name',
                'semester',
                'room',
                'instructor',
            ] as $field
        ) {
            $this->{$field} = $schedule->{$field};
        }

        $this->day_of_week = (string) $schedule->day_of_week;
        $this->starts_at = substr($schedule->starts_at, 0, 5);
        $this->ends_at = substr($schedule->ends_at, 0, 5);
        $this->is_active = $schedule->is_active;
        $this->showForm = true;
        $this->notice = '';

        $this->resetValidation();
    }

    private function writeSchedule(Closure $action): bool
    {
        try {
            Cache::lock('schedules:write', 15)->block(3, $action);

            return true;
        } catch (LockTimeoutException $exception) {
            $this->addError(
                'operation',
                'Jadwal sedang diperbarui pengguna lain. Coba kembali.'
            );

            return false;
        }
    }

    public function save(): void
    {
        Gate::authorize('manage-lab');

        $this->notice = '';
        $this->resetValidation();

        foreach (
            [
                'course_name',
                'class_name',
                'semester',
                'room',
                'instructor',
            ] as $field
        ) {
            $this->{$field} = Str::squish($this->{$field});
        }

        $this->room = mb_strtoupper($this->room);

        $data = $this->validate([
            'course_name' => ['required', 'string', 'max:100'],
            'class_name' => ['required', 'string', 'max:50'],
            'semester' => ['required', 'string', 'max:50'],
            'room' => ['required', 'string', 'max:50'],
            'instructor' => ['required', 'string', 'max:150'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => [
                'required',
                'date_format:H:i',
                'after:starts_at',
            ],
            'is_active' => ['boolean'],
        ], [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute maksimal :max karakter.',
            'starts_at.date_format' => 'Format jam mulai tidak valid.',
            'ends_at.date_format' => 'Format jam selesai tidak valid.',
            'ends_at.after' => 'Jam selesai harus setelah jam mulai.',
        ], [
            'course_name' => 'Mata kuliah',
            'class_name' => 'Kelas',
            'semester' => 'Semester',
            'room' => 'Ruangan',
            'instructor' => 'Pengajar/asisten',
            'day_of_week' => 'Hari',
            'starts_at' => 'Jam mulai',
            'ends_at' => 'Jam selesai',
        ]);

        $data['starts_at'] .= ':00';
        $data['ends_at'] .= ':00';

        $isNew = $this->editingId === null;

        $saved = $this->writeSchedule(function () use ($data) {
            $schedule = $this->editingId === null
                ? new Schedule()
                : Schedule::findOrFail($this->editingId);

            if ($data['is_active']) {
                $conflict = Schedule::query()
                    ->where('is_active', true)
                    ->where('semester', $data['semester'])
                    ->where('room', $data['room'])
                    ->where('day_of_week', $data['day_of_week'])
                    ->where('starts_at', '<', $data['ends_at'])
                    ->where('ends_at', '>', $data['starts_at'])
                    ->when(
                        $schedule->exists,
                        fn($query) => $query->where(
                            'id',
                            '!=',
                            $schedule->id
                        )
                    )
                    ->first();

                if ($conflict) {
                    $start = substr($conflict->starts_at, 0, 5);
                    $end = substr($conflict->ends_at, 0, 5);

                    throw ValidationException::withMessages([
                        'starts_at' => "Ruangan sudah digunakan untuk {$conflict->course_name} pada {$start}–{$end} WIB.",
                    ]);
                }
            }

            $schedule->fill($data)->save();
        });

        if (!$saved) {
            return;
        }

        $this->cancelEdit();

        $this->notice = $isNew
            ? 'Jadwal berhasil ditambahkan.'
            : 'Jadwal berhasil diperbarui.';
    }

    public function delete(int $id): void
    {
        Gate::authorize('manage-lab');

        $this->notice = '';
        $this->resetValidation('operation');

        $deleted = $this->writeSchedule(function () use ($id) {
            Schedule::findOrFail($id)->delete();
        });

        if (!$deleted) {
            return;
        }

        if ($this->editingId === $id) {
            $this->cancelEdit();
        }

        $this->notice = 'Jadwal berhasil dihapus.';
    }

    public function cancelEdit(): void
    {
        $this->reset(
            'editingId',
            'showForm',
            'course_name',
            'class_name',
            'semester',
            'room',
            'instructor',
            'day_of_week',
            'starts_at',
            'ends_at',
            'is_active'
        );

        $this->resetValidation();
    }

    public function render()
    {
        Gate::authorize('view-schedules');

        $search = mb_substr(trim($this->search), 0, 150);

        $schedules = Schedule::query()
            ->when(
                !Gate::allows('manage-lab'),
                fn($query) => $query->where('is_active', true)
            )
            ->when(
                $this->dayFilter !== '',
                fn($query) => $query->where(
                    'day_of_week',
                    $this->dayFilter
                )
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('course_name', 'like', "%{$search}%")
                        ->orWhere('class_name', 'like', "%{$search}%")
                        ->orWhere('room', 'like', "%{$search}%")
                        ->orWhere('instructor', 'like', "%{$search}%")
                        ->orWhere('semester', 'like', "%{$search}%");
                });
            })
            ->orderBy('day_of_week')
            ->orderBy('starts_at')
            ->orderBy('room')
            ->get();

        return view('livewire.schedules.index', [
            'schedules' => $schedules,
            'days' => Schedule::DAYS,
        ])->layout('components.layouts.app', [
            'title' => 'Jadwal Praktikum',
        ]);
    }
}
