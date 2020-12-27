<?php

declare(strict_types=1);

use Nette\Utils\Strings;
use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

require __DIR__ . '/../vendor/autoload.php';

// change prefix in PHPStan to separate it from another phpstan in vendor

$finder = new Finder();
$finder = $finder->in(__DIR__ . '/../rector-scoped/vendor/phpstan/phpstan-extracted')
    ->files();

$smartFileSystem = new SmartFileSystem();

$fileInfos = $finder->getIterator();

foreach (iterator_to_array($fileInfos) as $fileInfo) {
    /** @var SmartFileInfo $fileInfo */
    $originalFileContent = $fileInfo->getContents();

    $fileContent = Strings::replace($originalFileContent, '#(composerRequire|ComposerAutoloaderInit|ComposerStaticInit)\w+#ms', function (array $match) {
        return $match[0] . '__unique_rector';
    });

    // replace humbug prefix
    $fileContent = Strings::replace($fileContent, '#_HumbugBox\w+#ms', '$1__UniqueRector');

    // no change
    if ($fileContent === $originalFileContent) {
        continue;
    }

    // update file
    $smartFileSystem->dumpFile($fileInfo->getRealPath(), $fileContent);
}

