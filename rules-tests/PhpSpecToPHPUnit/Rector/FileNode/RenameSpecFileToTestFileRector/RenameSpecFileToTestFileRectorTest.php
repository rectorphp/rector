<?php

declare(strict_types=1);

namespace Rector\Tests\PhpSpecToPHPUnit\Rector\FileNode\RenameSpecFileToTestFileRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameSpecFileToTestFileRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);

        // test file is moved
        $isFileRemoved = $this->removedAndAddedFilesCollector->isFileRemoved($this->originalTempFileInfo);
        $this->assertTrue($isFileRemoved);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture', '*.php');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
