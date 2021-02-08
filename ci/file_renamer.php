<?php

// handy script for fast local operations

declare(strict_types=1);

use Nette\Utils\Strings;
use Symplify\SmartFileSystem\FileSystemFilter;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\Finder\SmartFinder;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;
use Webmozart\Assert\Assert;

require __DIR__ . '/../vendor/autoload.php';

// USE ↓

$fileRenamer = new FileRenamer();
$fileRenamer->rename(
    // paths
    [__DIR__ . '/../utils'],
    '*.php.inc',
    '#(\.php\.inc)$#',
    '.php'
);

// CODE ↓

final class FileRenamer
{
    /**
     * @var SmartFinder
     */
    private $smartFinder;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct()
    {
        $this->smartFinder = new SmartFinder(new FinderSanitizer(), new FileSystemFilter()); ;
        $this->smartFileSystem = new SmartFileSystem();
    }

    /**
     * @param string[] $sources
     */
    public function rename(array $sources, string $suffix, string $matchingRegex, string $replacement)
    {
        Assert::allString($sources);
        Assert::allFileExists($sources);

        $fileInfos = $this->smartFinder->find($sources, $suffix);

        $this->renameFileInfos($fileInfos, $matchingRegex, $replacement);
    }

    /**
     * @param SmartFileInfo[] $fileInfos
     */
    private function renameFileInfos(array $fileInfos, string $matchingRegex, string $replacement): void
    {
        foreach ($fileInfos as $fileInfo) {
            // do the rename
            $oldRealPath = $fileInfo->getRealPath();
            $newRealPath = Strings::replace($oldRealPath, $matchingRegex, $replacement);

            if ($oldRealPath === $newRealPath) {
                continue;
            }

            $this->smartFileSystem->rename($oldRealPath, $newRealPath);
        }
    }
}
