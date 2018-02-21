<?php declare(strict_types=1);

namespace Rector\PharBuilder\Filesystem;

use Closure;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class PharFilesFinder
{
    public function createFinderWithAllFiles(string $buildDir): Finder
    {
        return (new Finder())
            ->files()
            ->ignoreVCS(true)
            ->name('*.{yml,php}')
            ->in([
                $buildDir . '/bin',
                $buildDir . '/src',
                $buildDir . '/packages',
                $buildDir . '/vendor',
            ])
            ->exclude(['tests', 'docs', 'Tests', 'phpunit'])
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
