<?php declare(strict_types=1);

namespace Rector\Utils;

use Nette\Utils\Strings;
use Rector\Exception\FileSystem\DirectoryNotFoundException;
use Rector\Exception\FileSystem\FileNotFoundException;
use function Safe\glob;
use function Safe\sprintf;

final class FilesystemTweaker
{
    /**
     * @param string[] $source
     * @return string[][]
     */
    public function splitSourceToDirectoriesAndFiles(array $source): array
    {
        $files = [];
        $directories = [];

        foreach ($source as $singleSource) {
            if (is_file($singleSource)) {
                $this->ensureFileExists($singleSource);
                $files[] = $singleSource;
            } else {
                $directories[] = $singleSource;
            }
        }

        return [$files, $directories];
    }

    /**
     * This will turn paths like "src/Symfony/Component/*\/Tests" to existing directory paths
     *
     * @param string[] $directories
     * @return string[]
     */
    public function resolveDirectoriesWithFnmatch(array $directories): array
    {
        $absoluteDirectories = [];
        foreach ($directories as $directory) {
            if (Strings::contains($directory, '*')) { // is fnmatch for directories
                $absoluteDirectories = array_merge($absoluteDirectories, glob($directory, GLOB_ONLYDIR));
            } else { // is classic directory
                $this->ensureDirectoryExists($directory);
                $absoluteDirectories[] = $directory;
            }
        }

        return $absoluteDirectories;
    }

    private function ensureFileExists(string $file): void
    {
        if (file_exists($file)) {
            return;
        }

        throw new FileNotFoundException(sprintf('File "%s" was not found.', $file));
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (file_exists($directory)) {
            return;
        }

        throw new DirectoryNotFoundException(sprintf('Directory "%s" was not found.', $directory));
    }
}
