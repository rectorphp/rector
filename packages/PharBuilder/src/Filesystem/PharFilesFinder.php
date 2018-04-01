<?php declare(strict_types=1);

namespace Rector\PharBuilder\Filesystem;

use Closure;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class PharFilesFinder
{
    public function createForDirectory(string $directory): Finder
    {
        return (new Finder())
            ->files()
            ->ignoreVCS(true)
            ->name('*.{yml,php}')
            // "in()" and "path()" have to be split to make SplFileInfo "getRelativePathname()" get path from $directory
            ->in($directory)
            ->path('#(bin|src|packages)#') // |vendor
            ->exclude(
                ['tests', 'docs', 'Tests', 'Testing', 'phpunit', 'sebastianbergman', 'vendor', 'packages/PharBuilder']
            )
            ->sort($this->sortFilesByName());
    }

    private function sortFilesByName(): Closure
    {
        return function (SplFileInfo $firstFileInfo, SplFileInfo $secondFileInfo) {
            return strcmp(
                strtr($firstFileInfo->getRealPath(), '\\', '/'),
                strtr($secondFileInfo->getRealPath(), '\\', '/')
            );
        };
    }
}
