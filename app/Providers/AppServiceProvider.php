<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Submission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-lab', function (User $user): bool {
            return in_array($user->role, ['admin', 'asisten'], true);
        });

        Gate::define('manage-users', function (User $user): bool {
            return $user->is_active && $user->role === 'admin';
        });

        Gate::define('view-materials', function (?User $user = null): bool {
            return true;
        });

        Gate::define('manage-categories', function (User $user): bool {
            return $user->role === 'admin';
        });

        Gate::define('view-assignments', function (User $user): bool {
            return in_array(
                $user->role,
                ['admin', 'asisten', 'mahasiswa'],
                true
            );
        });

        Gate::define('submit-assignment', function (User $user): bool {
            return $user->role === 'mahasiswa';
        });

        Gate::define('download-submission', function (
            User $user,
            Submission $submission
        ): bool {
            return in_array($user->role, ['admin', 'asisten'], true);
        });

        Gate::define('view-schedules', function (User $user): bool {
            return in_array(
                $user->role,
                ['admin', 'asisten', 'mahasiswa'],
                true
            );
        });
    }
}
