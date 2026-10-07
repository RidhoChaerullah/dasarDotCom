<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Banner;
use App\Models\Schedule;
use App\Models\Submission;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        abort_unless($user?->is_active, 403);
        $staff = Gate::allows('manage-lab');
        $open = Assignment::query()->where('is_open', true)
            ->where(fn($query) => $query->where('due_at', '>=', now())->orWhere('allow_late', true));
        $today = now('Asia/Jakarta');
        $schedules = Schedule::where('is_active', true)->where('day_of_week', $today->dayOfWeekIso)
            ->orderBy('starts_at')->orderBy('room')->get();
        return view('dashboard', [
            'staff' => $staff,
            'openCount' => (clone $open)->count(),
            'submissionCount' => Submission::query()->when(! $staff, fn($q) => $q->where('user_id', $user->id))->count(),
            'assignments' => (clone $open)->with(['submissions' => fn($q) => $q->where('user_id', $user->id)])
                ->orderByRaw('CASE WHEN due_at >= ? THEN 0 ELSE 1 END', [now()])
                ->orderBy('due_at')->limit(5)->get(),
            'schedules' => $schedules,
            'today' => $today,
            'banners' => Banner::where('is_active', true)->orderBy('sort_order')->orderByDesc('id')->get(),
        ]);
    }
}
