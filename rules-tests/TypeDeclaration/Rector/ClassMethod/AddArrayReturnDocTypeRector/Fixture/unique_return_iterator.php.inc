<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class UniqueReturnIterator
{
    public function provideData(): Iterator
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

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class UniqueReturnIterator
{
    /**
     * @return \Iterator<\Rector\FileSystemRector\ValueObject\AddedFileWithContent[]|\Symplify\SmartFileSystem\SmartFileInfo[]>
     */
    public function provideData(): Iterator
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
