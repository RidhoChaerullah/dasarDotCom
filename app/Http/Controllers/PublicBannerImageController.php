<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class PublicBannerImageController extends Controller
{
    public function __invoke(Banner $banner)
    {
        abort_unless($banner->is_active, 404);
        abort_unless(
            preg_match('/\Aimages\/[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)\z/', $banner->image_path),
            404
        );

        $disk = Storage::disk('banners');
        abort_unless($disk->exists($banner->image_path), 404);

        return $disk->response($banner->image_path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
