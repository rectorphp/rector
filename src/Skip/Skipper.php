<?php

declare(strict_types=1);

namespace Rector\Core\Skip;

use Rector\Core\Rector\AbstractRector;
use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Skipper
{
    /**
     * @var mixed[]
     */
    private $skip = [];

    /**
     * @param mixed[] $skip
     */
    public function __construct(array $skip = [])
    {
        $this->skip = $skip;
    }

    private function shouldSkipFileInfoAndRule(SmartFileInfo $smartFileInfo, AbstractRector $rector): bool
    {
        if ($this->skip === []) {
            return false;
        }
        $rectorClass = get_class($rector);
        if (! array_key_exists($rectorClass, $this->skip)) {
            return false;
        }

        $locations = $this->skip[$rectorClass];
        $filePathName = $smartFileInfo->getPathName();

        foreach ($locations as $location) {
            if (is_dir($location)) {
                $finder = new Finder();
                $finder->files()->in($location)->name('*.php');

                if ($this->isFoundInDirectory($finder, $filePathName)) {
                    return true;
                }
            }

            if ($location === $filePathName) {
                return true;
            }
        }

        return false;
    }

    private function isFoundInDirectory(Finder $finder, string $filePathName): bool
    {
        foreach ($finder as $file) {
            if ($file->getRealPath() === $filePathName) {
                return true;
            }
        }

        return false;
    }
}
