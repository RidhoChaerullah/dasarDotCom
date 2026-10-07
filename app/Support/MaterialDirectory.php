<?php

namespace App\Support;

class MaterialDirectory
{
    public function root(): string
    {
        clearstatcache(true);

        $configured = config('filesystems.disks.materials.root');

        abort_unless(
            is_string($configured) && $configured !== '',
            503,
            'Lokasi folder materi belum dikonfigurasi.'
        );

        $root = realpath($configured);

        abort_unless(
            $root !== false && is_dir($root) && is_readable($root),
            503,
            'Folder materi belum tersedia atau tidak dapat dibaca.'
        );

        return $root;
    }

    public function resolve(string $relative): ?string
    {
        $root = $this->root();

        if ($relative === '') {
            return $root;
        }

        // Hanya menerima path relatif dengan pemisah "/".
        abort_if(
            preg_match('/[\\\\:\x00-\x1F]/', $relative) === 1,
            403
        );

        $segments = explode('/', $relative);
        $candidate = $root;

        foreach ($segments as $segment) {
            abort_if(
                $segment === ''
                    || str_starts_with($segment, '.')
                    || $segment !== rtrim($segment, ". ")
                    || in_array(strtolower($segment), ['desktop.ini', 'thumbs.db'], true),
                403
            );

            $candidate .= DIRECTORY_SEPARATOR . $segment;

            // Jangan ikuti symbolic link yang ditempatkan di dalam materi.
            abort_if(is_link($candidate), 403);
        }

        clearstatcache(true);

        $resolved = realpath($candidate);

        if ($resolved === false) {
            return null;
        }

        $base = rtrim(str_replace('\\', '/', $root), '/');
        $target = str_replace('\\', '/', $resolved);

        if (PHP_OS_FAMILY === 'Windows') {
            $base = strtolower($base);
            $target = strtolower($target);
        }

        // Termasuk pemeriksaan junction/path yang keluar dari root.
        abort_unless(str_starts_with($target, $base . '/'), 403);

        return $resolved;
    }

    public function entries(string $relative): array
    {
        $directory = $this->resolve($relative);

        abort_unless($directory && is_dir($directory), 404);

        $names = @scandir($directory);

        abort_if($names === false, 503, 'Folder materi tidak dapat dibaca.');

        $entries = [];

        foreach ($names as $name) {
            if (
                str_starts_with($name, '.')
                || in_array(strtolower($name), ['desktop.ini', 'thumbs.db'], true)
            ) {
                continue;
            }

            $path = $relative === '' ? $name : $relative . '/' . $name;

            try {
                $absolute = $this->resolve($path);
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
                if ($exception->getStatusCode() === 403) {
                    continue;
                }

                throw $exception;
            }

            if (!$absolute || !is_readable($absolute)) {
                continue;
            }

            $isDirectory = is_dir($absolute);

            if (!$isDirectory && !is_file($absolute)) {
                continue;
            }

            $entries[] = [
                'name' => $name,
                'path' => $path,
                'directory' => $isDirectory,
                'size' => $isDirectory ? 0 : (@filesize($absolute) ?: 0),
            ];
        }

        usort($entries, function (array $a, array $b): int {
            return ($b['directory'] <=> $a['directory'])
                ?: strnatcasecmp($a['name'], $b['name']);
        });

        return $entries;
    }
}
