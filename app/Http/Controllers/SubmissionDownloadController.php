<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SubmissionDownloadController extends Controller
{
    public function __invoke(Submission $submission)
    {
        Gate::authorize('download-submission', $submission);

        $disk = Storage::disk('submissions');

        abort_unless(
            $disk->exists($submission->file_path),
            404,
            'File submission tidak ditemukan.'
        );

        return $disk->download(
            $submission->file_path,
            $submission->stored_filename,
            [
                'Content-Type' => 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }
}
