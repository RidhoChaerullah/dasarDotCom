<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class SubmissionDirectory
{
    public static function valid(?string $relative): bool
    {
        if (
            !$relative
            || strlen($relative) > 255
            || preg_match('/[\\\\:\x00-\x1F]/', $relative)
        ) {
            return false;
        }

        foreach (explode('/', $relative) as $segment) {
            if (
                $segment === ''
                || str_starts_with($segment, '.')
                || $segment !== rtrim($segment, ". ")
            ) {
                return false;
            }
        }

        clearstatcache(true);

        $root = realpath(config('filesystems.disks.submissions.root'));

        if ($root === false) {
            return false;
        }

        $target = realpath($root . DIRECTORY_SEPARATOR . $relative);

        if ($target === false || !is_dir($target)) {
            return false;
        }

        $root = rtrim(str_replace('\\', '/', $root), '/');
        $target = str_replace('\\', '/', $target);

        if (PHP_OS_FAMILY === 'Windows') {
            $root = strtolower($root);
            $target = strtolower($target);
        }

        return str_starts_with($target, $root . '/');
    }

    public static function options(): array
    {
        $root = config('filesystems.disks.submissions.root');

        if (!is_dir($root)) {
            return [];
        }

        $directories = array_values(array_filter(
            Storage::disk('submissions')->allDirectories(),
            fn(string $path) => self::valid($path)
        ));

        natcasesort($directories);

        return array_values($directories);
    }
}
