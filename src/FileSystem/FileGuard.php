<?php declare(strict_types=1);

namespace Rector\FileSystem;

use Rector\Exception\FileSystem\DirectoryNotFoundException;
use Rector\Exception\FileSystem\FileNotFoundException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

final class FileGuard
{
    public static function ensureFileExists(string $file, string $location): void
    {
        if (file_exists($file)) {
            return;
        }

        throw new FileNotFoundException(sprintf(
            'File "%s" not found in "%s".',
            $file,
            $location
        ));
    }
}
