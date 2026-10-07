<?php

namespace App\Http\Controllers;

use App\Support\MaterialDirectory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MaterialDownloadController extends Controller
{
    public function __invoke(
        Request $request,
        MaterialDirectory $directory
    ) {
        Gate::authorize('view-materials');

        $data = $request->validate([
            'path' => ['required', 'string', 'max:2048'],
        ]);

        $absolute = $directory->resolve($data['path']);

        abort_unless(
            $absolute && is_file($absolute) && is_readable($absolute),
            404,
            'File sudah dipindahkan, dihapus, atau tidak dapat dibaca.'
        );

        return response()->download(
            $absolute,
            basename($absolute),
            [
                'Content-Type' => 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }
}
