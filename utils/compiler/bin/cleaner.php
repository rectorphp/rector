<?php declare(strict_types=1);

use Symplify\SmartFileSystem\FileSystemFilter;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\Finder\SmartFinder;
use Symplify\SmartFileSystem\SmartFileSystem;

require __DIR__ . '/../../../vendor/autoload.php';

// cleans all useless files
$smartFinder = new SmartFinder(new FinderSanitizer(), new FileSystemFilter());
$sartFileSystem = new SmartFileSystem();

// @todo normal commadn with arugment?

$fileInfos = $smartFinder->find([
    __DIR__ . '/../../../packages',
    __DIR__ . '/../../../src',
    __DIR__ . '/../../../rules',
    __DIR__ . '/../../../tests',
], '#.(\.php\.inc|Test\.php)$#');

echo sprintf('Found %d files to be deleted', count($fileInfos));

foreach ($fileInfos as $fileInfo) {
    echo $fileInfo->getRelativeFilePathFromCwd() . PHP_EOL;
}

$sartFileSystem->remove($fileInfos);
