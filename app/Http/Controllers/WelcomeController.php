<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __invoke(): View
    {
        return view('welcome', [
            'banners' => Banner::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get(),
        ]);
    }
}
