<?php

declare(strict_types=1);

namespace Rector\Tests\Restoration\Rector\ClassLike\UpdateFileNameByClassNameFileSystemRector;

use Iterator;
use Nette\Utils\FileSystem;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UpdateFileNameByClassNameFileSystemRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $smartFileInfo): void
    {
        $this->doTestFileInfo($smartFileInfo);

        $expectedAddedFileWithContent = new AddedFileWithContent(
            $this->originalTempFileInfo->getRealPathDirectory() . '/SkipDifferentClassName.php',
            FileSystem::read(__DIR__ . '/Fixture/skip_different_class_name.php.inc')
        );
        $this->assertFileWasAdded($expectedAddedFileWithContent);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
