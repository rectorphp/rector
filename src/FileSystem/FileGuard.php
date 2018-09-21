<?php declare(strict_types=1);

namespace Rector\FileSystem;

use Rector\Exception\FileSystem\FileNotFoundException;
use function Safe\sprintf;

final class FileGuard
{
    public function ensureFileExists(string $file, string $location): void
    {
        if (is_file($file) && file_exists($file)) {
            return;
        }

        throw new FileNotFoundException(sprintf('File "%s" not found in "%s".', $file, $location));
    }
}
