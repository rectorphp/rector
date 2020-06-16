<?php

declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Rector;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameTesterTestToPHPUnitToTestFileRector extends AbstractFileSystemRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename "*.phpt" file to "*Test.php" file', [
            new CodeSample(
                <<<'CODE_SAMPLE'
// tests/SomeTestCase.phpt
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
// tests/SomeTestCase.php
CODE_SAMPLE
            ),
        ]);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $oldRealPath = $smartFileInfo->getRealPath();
        if (! Strings::endsWith($oldRealPath, '*.phpt')) {
            return;
        }

        $newRealPath = $this->createNewRealPath($oldRealPath);
        if ($newRealPath === $oldRealPath) {
            return;
        }

        // rename
        FileSystem::rename($oldRealPath, $newRealPath);

        // remove old file
        $this->removeFile($smartFileInfo);
    }

    private function createNewRealPath(string $oldRealPath): string
    {
        // file suffix
        $newRealPath = Strings::replace($oldRealPath, '#\.phpt$#', '.php');

        // Test suffix
        if (! Strings::endsWith($newRealPath, 'Test.php')) {
            $newRealPath = Strings::replace($newRealPath, '#\.php$#', 'Test.php');
        }

        return $newRealPath;
    }
}
