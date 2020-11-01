<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\FileNode\RenameSpecFileToTestFileRector;

use Iterator;
use Nette\Utils\Strings;
use Rector\FileSystemRector\Contract\MovedFileInterface;
use Rector\PhpSpecToPHPUnit\Rector\FileNode\RenameSpecFileToTestFileRector;
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
        $movedFile = $this->matchMovedFile($this->originalTempFileInfo);
        $this->assertInstanceOf(MovedFileInterface::class, $movedFile);

        $this->assertTrue(Strings::endsWith($movedFile->getNewPathname(), 'Test.php'));
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture', '*.php');
    }

    protected function getRectorClass(): string
    {
        return RenameSpecFileToTestFileRector::class;
    }
}
