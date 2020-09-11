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
     * @var mixed[]
     */
    private static $filesInDirectory = [];

    /**
     * @param mixed[] $skip
     */
    public function __construct(array $skip = [])
    {
        $this->skip = $skip;
    }

    public function shouldSkipFileInfoAndRule(SmartFileInfo $smartFileInfo, AbstractRector $rector): bool
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
            if (is_dir($location) && $this->isFoundInDirectory($location, $filePathName)) {
                return true;
            }

            if ($location === $filePathName) {
                return true;
            }
        }

        return false;
    }

    private function isFoundInDirectory(string $location, string $filePathName): bool
    {
        if (! array_key_exists($location, self::$filesInDirectory)) {
            $finder = new Finder();
            $finder->files()->in($location)->name('*.php');

            foreach ($finder as $file) {
                self::$filesInDirectory[$location][] = $file->getRealPath();
            }
        }

        return in_array($filePathName, self::$filesInDirectory[$location], true);
    }
}
