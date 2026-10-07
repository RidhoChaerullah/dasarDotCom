<?php

use App\Http\Controllers\MaterialDownloadController;
use App\Http\Controllers\SubmissionDownloadController;
use App\Livewire\Assignments\Index as AssignmentsIndex;
use App\Livewire\Assignments\Show as AssignmentShow;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Management\Index as ManagementIndex;
use App\Livewire\Materials\Index as MaterialsIndex;
use App\Livewire\SubmissionCategories\Index as SubmissionCategoriesIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\Schedules\Index as SchedulesIndex;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Profile\Index as ProfileIndex;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', \App\Livewire\Auth\Portal::class)->name('login');
    Route::get('/register', \App\Livewire\Auth\Portal::class)->name('register');
});

Route::middleware('can:view-materials')->group(function () {
    Route::get('/materi', MaterialsIndex::class)
        ->name('materials.index');

    Route::get('/materi/download', MaterialDownloadController::class)
        ->name('materials.download');
});

Route::middleware('auth')->group(function () {
    Route::get('/pengelolaan/banner', \App\Livewire\Banners\Index::class)
        ->middleware('can:manage-lab')->name('banners.index');
    Route::get('/banner/{banner}/gambar', \App\Http\Controllers\BannerImageController::class)
        ->name('banners.image');
    Route::get('/dashboard', \App\Http\Controllers\DashboardController::class)->name('dashboard');

    Route::post('/logout', function (Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    Route::get('/pengelolaan', ManagementIndex::class)
        ->middleware('can:manage-lab')
        ->name('management.index');

    Route::get('/pengelolaan/kategori', SubmissionCategoriesIndex::class)
        ->middleware('can:manage-categories')
        ->name('submission-categories.index');

    Route::get('/tugas', AssignmentsIndex::class)
        ->middleware('can:view-assignments')
        ->name('assignments.index');

    Route::get('/tugas/{assignment}', AssignmentShow::class)
        ->middleware('can:view-assignments')
        ->name('assignments.show');

    Route::get('/submission/{submission}/download', SubmissionDownloadController::class)
        ->name('submissions.download');

    Route::get('/jadwal', SchedulesIndex::class)
        ->middleware('can:view-schedules')
        ->name('schedules.index');

    Route::get('/pengelolaan/pengguna', UsersIndex::class)
        ->middleware('can:manage-users')
        ->name('users.index');

    Route::get('/profil', ProfileIndex::class)
        ->name('profile.index');
});
