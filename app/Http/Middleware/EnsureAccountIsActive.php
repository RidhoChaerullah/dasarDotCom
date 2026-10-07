<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->is_active) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->hasHeader('X-Livewire')) {
                abort(419, 'Sesi berakhir. Silakan masuk kembali.');
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Akun kamu sedang dinonaktifkan. Hubungi Admin.',
            ]);
        }

        return $next($request);
    }
}
