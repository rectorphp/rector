<?php declare(strict_types=1);

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

require __DIR__ . '/vendor/autoload.php';

// 1. rename files
$finder = (new Finder())->files()
    ->in([__DIR__ . '/packages', __DIR__ . '/tests'])
    ->name('#(wrong|Wrong)#');

/** @var SplFileInfo $fileInfo */
foreach ($finder as $fileInfo) {
    $newName = Strings::replace($fileInfo->getRealPath(), '#wrong#', 'fixture');
    FileSystem::rename($fileInfo->getRealPath(), $newName);
    // remove old file
    unlink($fileInfo->getRealPath());
}
