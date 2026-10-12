<?php

use App\Http\Controllers\PublicBannerImageController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home');
Route::get('/kegiatan/banner/{banner}/gambar', PublicBannerImageController::class)
    ->name('welcome.banners.image');
