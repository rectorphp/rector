<?php

declare(strict_types=1);

namespace Rector\Core\FileSystem;

use Nette\Utils\Strings;
use Symplify\SmartFileSystem\FileSystemGuard;

final class FilesystemTweaker
{
    /**
     * @var FileSystemGuard
     */
    private $fileSystemGuard;

    public function __construct(FileSystemGuard $fileSystemGuard)
    {
        $this->fileSystemGuard = $fileSystemGuard;
    }

    /**
     * This will turn paths like "src/Symfony/Component/*\/Tests" to existing directory paths
     *
     * @param string[] $directories
     *
     * @return string[]
     */
    public function resolveDirectoriesWithFnmatch(array $directories): array
    {
        $absoluteDirectories = [];
        foreach ($directories as $directory) {
            // is fnmatch for directories
            if (Strings::contains($directory, '*')) {
                $foundDirectories = $this->foundDirectoriesInGlob($directory);
                $absoluteDirectories = array_merge($absoluteDirectories, $foundDirectories);
            } else {
                // is classic directory
                $this->fileSystemGuard->ensureDirectoryExists($directory);
                $absoluteDirectories[] = $directory;
            }
        }

        return $absoluteDirectories;
    }

    /**
     * This will turn paths like "src/Symfony/Component/**\/some.php" to existing file paths
     *
     * @param string[] $filesAndDirectories
     *
     * @return string[]
     */
    public function resolveFilesWithFnmatch(array $filesAndDirectories): array
    {
        $files = [];

        foreach ($filesAndDirectories as $filesAndDirectory) {
            if (! Strings::contains($filesAndDirectory, '*')) {
                continue;
            }

            foreach ((array) glob($filesAndDirectory) as $foundPath) {
                if (! is_string($foundPath)) {
                    continue;
                }

                if (! is_file($foundPath)) {
                    continue;
                }

                $files[] = $foundPath;
            }
        }

        return array_values($files);
    }

    /**
     * @return string[]
     */
    private function foundDirectoriesInGlob(string $directory): array
    {
        $foundDirectories = [];

        foreach ((array) glob($directory, GLOB_ONLYDIR) as $foundDirectory) {
            if (! is_string($foundDirectory)) {
                continue;
            }

            $foundDirectories[] = $foundDirectory;
        }

        return $foundDirectories;
    }
}
