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

    private function isFoundInDirectory(string $directory, string $filePathName): bool
    {
        $directory = rtrim($directory, '/') . '/';
        if (! array_key_exists($directory, self::$filesInDirectory)) {
            $finder = new Finder();
            $finder->files()->in($directory)->name('*.php');

            foreach ($finder as $file) {
                self::$filesInDirectory[$directory][] = $file->getRealPath();
            }
        }

        return in_array($filePathName, self::$filesInDirectory[$directory], true);
    }
}
