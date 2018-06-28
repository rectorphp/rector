<?php declare(strict_types=1);

namespace Rector\FileSystem;

use Nette\Utils\Strings;
use Rector\Exception\FileSystem\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class FilesFinder
{
    /**
     * @param string[] $source
     * @param string[] $suffixes
     * @return SplFileInfo[]
     */
    public function findInDirectoriesAndFiles(array $source, array $suffixes): array
    {
        $files = [];
        $directories = [];

        foreach ($source as $singleSource) {
            if (is_file($singleSource)) {
                $files[] = new SplFileInfo($singleSource, '', '');
            } else {
                $directories[] = $singleSource;
            }
        }

        if (count($directories)) {
            $files = array_merge($files, $this->findInDirectories($directories, $suffixes));
        }

        return $files;
    }

    /**
     * @param string[] $directories
     * @param string[] $suffixes
     * @return SplFileInfo[]
     */
    private function findInDirectories(array $directories, array $suffixes): array
    {
        $this->ensureDirectoriesExist($directories);

        $suffixes = $this->normalizeSuffixesToPattern($suffixes);

        $finder = Finder::create()
            ->files()
            ->name($suffixes)
            ->in($directories)
            ->exclude(['examples', 'Examples', 'stubs', 'Stubs', 'fixtures', 'Fixtures', 'polyfill', 'Polyfill'])
            ->notName('*polyfill*')
            ->sortByName();

        return iterator_to_array($finder->getIterator());
    }

    /**
     * @param string[] $directories
     */
    private function ensureDirectoriesExist(array $directories): void
    {
        foreach ($directories as $directory) {
            if (file_exists($directory)) {
                continue;
            }

            throw new DirectoryNotFoundException(sprintf('Directory "%s" was not found.', $directory));
        }
    }

    /**
     * @param string[] $suffixes
     */
    private function normalizeSuffixesToPattern(array $suffixes): string
    {
        $suffixesPattern = '';
        foreach ($suffixes as $suffix) {
            $suffixesPattern = Strings::startsWith($suffix, '*') ? $suffix : '*' . $suffix;
        }

        return $suffixesPattern;
    }
}
