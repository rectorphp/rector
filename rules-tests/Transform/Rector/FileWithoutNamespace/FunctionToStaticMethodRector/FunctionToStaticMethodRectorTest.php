<?php

declare(strict_types=1);

namespace Rector\Tests\Transform\Rector\FileWithoutNamespace\FunctionToStaticMethodRector;

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FunctionToStaticMethodRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $smartFileInfo): void
    {
        $this->doTestFileInfo($smartFileInfo);

        $addedFileWithContent = new AddedFileWithContent(
            $this->originalTempFileInfo->getRealPathDirectory() . '/StaticFunctions.php',
            file_get_contents(__DIR__ . '/Source/ExpectedStaticFunctions.php')
        );

        $this->assertFileWasAdded($addedFileWithContent);
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
