<?php

declare(strict_types=1);

namespace Rector\Tests\NetteToSymfony\Rector\Class_\FormControlToControllerAndFormTypeRector;

use Iterator;
use Nette\Utils\FileSystem;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FormControlToControllerAndFormTypeRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo, AddedFileWithContent $expectedAddedFileWithContent): void
    {
        $this->doTestFileInfo($fileInfo);
        $this->assertFileWasAdded($expectedAddedFileWithContent);
    }

    /**
     * @return Iterator<string[]|SmartFileInfo[]>
     */
    public function provideData(): Iterator
    {
        yield [
            new SmartFileInfo(__DIR__ . '/Fixture/fixture.php.inc'),
            new AddedFileWithContent(
                'src/Controller/SomeFormController.php',
                FileSystem::read(__DIR__ . '/Source/extra_file.php')
            ),
        ];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
