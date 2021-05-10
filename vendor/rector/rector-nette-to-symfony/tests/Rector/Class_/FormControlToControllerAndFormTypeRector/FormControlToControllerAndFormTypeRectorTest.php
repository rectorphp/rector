<?php

declare (strict_types=1);
namespace Rector\NetteToSymfony\Tests\Rector\Class_\FormControlToControllerAndFormTypeRector;

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20210510\Symplify\SmartFileSystem\SmartFileSystem;
final class FormControlToControllerAndFormTypeRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo, AddedFileWithContent $expectedAddedFileWithContent) : void
    {
        $this->doTestFileInfo($fileInfo);
        $this->assertFileWasAdded($expectedAddedFileWithContent);
    }
    /**
     * @return Iterator<SmartFileInfo[]|AddedFileWithContent[]>
     */
    public function provideData() : Iterator
    {
        $smartFileSystem = new SmartFileSystem();
        (yield [new SmartFileInfo(__DIR__ . '/Fixture/fixture.php.inc'), new AddedFileWithContent('src/Controller/SomeFormController.php', $smartFileSystem->readFile(__DIR__ . '/Source/extra_file.php'))]);
    }
    public function provideConfigFilePath() : string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
