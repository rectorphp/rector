<?php declare(strict_types=1);

namespace Rector\FileSystem;

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
        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->exclude('examples')
            ->exclude('tests')
            ->exclude('Tests')
            ->exclude('Test')
            ->in($directories);

        return iterator_to_array($finder->getIterator());
    }
}
