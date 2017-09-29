<?php declare(strict_types=1);

namespace Rector\FileSystem;

use Rector\Exception\FileSystem\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class PhpFilesFinder
{
    /**
     * @param string[] $directories
     * @return SplFileInfo[]
     */
    public function findInDirectories(array $directories): array
    {
        $this->ensureDirectoriesExist($directories);

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in($directories)
            ->exclude('examples')
            ->exclude('tests')
            ->exclude('Tests')
            ->exclude('Test')
            ->exclude('stubs')
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

            throw new DirectoryNotFoundException(sprintf(
                'Directory "%s" was not found.',
                $directory
            ));
        }
    }
}
