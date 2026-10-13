<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class PlainTextUserProvider extends EloquentUserProvider
{
    // Override method ini agar membandingkan teks biasa, bukan hash
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['password'];
        return $user->getAuthPassword() === $plain;
    }
}