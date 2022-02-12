<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Generator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class UniqueReturnGenerator
{
    public function provideData(): Generator
    {
        $smartFileSystem = new SmartFileSystem();

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Entity/RandomInterface.php'),
            new AddedFileWithContent(
                '/Source/Contract/RandomInterface.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/ExpectedRandomInterface.php')
            )
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Control/ControlFactory.php'),
            new AddedFileWithContent(
                '/Source/Control/ControlFactory.php',
                $smartFileSystem->readFile(__DIR__ . '/Source/Control/ControlFactory.php')
            ),
        ];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Generator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class UniqueReturnGenerator
{
    /**
     * @return \Generator<\Rector\FileSystemRector\ValueObject\AddedFileWithContent[]|\Symplify\SmartFileSystem\SmartFileInfo[]>
     */
    public function provideData(): Generator
    {
        $smartFileSystem = new SmartFileSystem();

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Entity/RandomInterface.php'),
            new AddedFileWithContent(
                '/Source/Contract/RandomInterface.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/ExpectedRandomInterface.php')
            )
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Control/ControlFactory.php'),
            new AddedFileWithContent(
                '/Source/Control/ControlFactory.php',
                $smartFileSystem->readFile(__DIR__ . '/Source/Control/ControlFactory.php')
            ),
        ];
    }
}

?>
