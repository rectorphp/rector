<?php

declare (strict_types=1);
namespace Rector\Testing\Fixture;

use Iterator;
use RectorPrefix202208\Nette\Utils\Strings;
use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix202208\Symfony\Component\Finder\Finder;
use RectorPrefix202208\Symfony\Component\Finder\SplFileInfo;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix202208\Symplify\SmartFileSystem\Exception\FileNotFoundException;
final class FixtureFileFinder
{
    /**
     * @return Iterator<array<int, SmartFileInfo>>
     */
    public static function yieldDirectory(string $directory, string $suffix = '*.php.inc') : Iterator
    {
        $fileInfos = self::findFilesInDirectory($directory, $suffix);
        foreach ($fileInfos as $fileInfo) {
            // @todo is this one needed? maybe symfony is good enough :)
            $smartFileInfo = new SmartFileInfo($fileInfo->getRealPath());
            (yield [$smartFileInfo]);
        }
    }
    /**
     * @return SplFileInfo[]
     */
    private static function findFilesInDirectory(string $directory, string $suffix) : array
    {
        $finder = (new Finder())->in($directory)->files()->name($suffix);
        $fileInfos = \iterator_to_array($finder);
        return \array_values($fileInfos);
    }
    /**
     * @return Iterator<array<int, SmartFileInfo>>
     */
    public static function yieldDirectoryExclusively(string $directory, string $suffix = '*.php.inc') : Iterator
    {
        $fileInfos = self::findFilesInDirectoryExclusively($directory, $suffix);
        return self::yieldFileInfos($fileInfos);
    }
    private static function ensureNoOtherFileName(string $directory, string $suffix) : void
    {
        $finder = Finder::create()->in($directory)->files()->notName($suffix);
        /** @var SplFileInfo[] $fileInfos */
        $fileInfos = \iterator_to_array($finder->getIterator());
        $relativeFilePaths = [];
        foreach ($fileInfos as $fileInfo) {
            $relativeFilePaths[] = Strings::substring($fileInfo->getRealPath(), \strlen(\getcwd()) + 1);
        }
        if ($relativeFilePaths === []) {
            return;
        }
        throw new ShouldNotHappenException(\sprintf('Files "%s" have invalid suffix, use "%s" suffix instead', \implode('", ', $relativeFilePaths), $suffix));
    }
    /**
     * @return SplFileInfo[]
     */
    private static function findFilesInDirectoryExclusively(string $directory, string $suffix) : array
    {
        self::ensureNoOtherFileName($directory, $suffix);
        $finder = Finder::create()->in($directory)->files()->name($suffix);
        $fileInfos = \iterator_to_array($finder->getIterator());
        return \array_values($fileInfos);
    }
    /**
     * @param SplFileInfo[] $fileInfos
     * @return Iterator<array<int, SmartFileInfo>>
     */
    private static function yieldFileInfos(array $fileInfos) : Iterator
    {
        foreach ($fileInfos as $fileInfo) {
            try {
                $smartFileInfo = new SmartFileInfo($fileInfo->getRealPath());
                (yield [$smartFileInfo]);
            } catch (FileNotFoundException $exception) {
            }
        }
    }
}
