<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class AddIteratorFileUnion
{
    public function provideData(): Iterator
    {
        $smartFileSystem = new SmartFileSystem();
        $extraFileInfos = [
            new SmartFileInfo(__DIR__ . '/Source/RandomInterfaceUseCase.php'),
            new SmartFileInfo(__DIR__ . '/Source/ValueObject/SameClassImplementEntity.php'),
            new SmartFileInfo(__DIR__ . '/Source/Entity/RandomInterfaceUseCaseInTheSameNamespace.php'),
        ];
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Entity/RandomInterface.php'),
            new AddedFileWithContent(
                '/Source/Contract/RandomInterface.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/ExpectedRandomInterface.php')
            ),
            // extra files
            $extraFileInfos,
        ];
        // skip nette control factory
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Control/ControlFactory.php'),
            new AddedFileWithContent(
                '/Source/Control/ControlFactory.php',
                $smartFileSystem->readFile(__DIR__ . '/Source/Control/ControlFactory.php')
            ),
        ];
        // skip form control factory, even in docblock
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Control/FormFactory.php'),
            new AddedFileWithContent(
                '/Source/Control/FormFactory.php',
                $smartFileSystem->readFile(__DIR__ . '/Source/Control/FormFactory.php')
            ),
        ];
        // skip already in correct location
        yield [new SmartFileInfo(__DIR__ . '/Source/Contract/KeepThisSomeInterface.php'), null];
        // skip already in correct location
        yield [new SmartFileInfo(__DIR__ . '/Source/Contract/Foo/KeepThisSomeInterface.php'), null];
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class AddIteratorFileUnion
{
    /**
     * @return \Iterator<array<int, \Symplify\SmartFileSystem\SmartFileInfo[]>|\Rector\FileSystemRector\ValueObject\AddedFileWithContent[]|\Symplify\SmartFileSystem\SmartFileInfo[]|null[]>
     */
    public function provideData(): Iterator
    {
        $smartFileSystem = new SmartFileSystem();
        $extraFileInfos = [
            new SmartFileInfo(__DIR__ . '/Source/RandomInterfaceUseCase.php'),
            new SmartFileInfo(__DIR__ . '/Source/ValueObject/SameClassImplementEntity.php'),
            new SmartFileInfo(__DIR__ . '/Source/Entity/RandomInterfaceUseCaseInTheSameNamespace.php'),
        ];
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Entity/RandomInterface.php'),
            new AddedFileWithContent(
                '/Source/Contract/RandomInterface.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/ExpectedRandomInterface.php')
            ),
            // extra files
            $extraFileInfos,
        ];
        // skip nette control factory
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Control/ControlFactory.php'),
            new AddedFileWithContent(
                '/Source/Control/ControlFactory.php',
                $smartFileSystem->readFile(__DIR__ . '/Source/Control/ControlFactory.php')
            ),
        ];
        // skip form control factory, even in docblock
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Control/FormFactory.php'),
            new AddedFileWithContent(
                '/Source/Control/FormFactory.php',
                $smartFileSystem->readFile(__DIR__ . '/Source/Control/FormFactory.php')
            ),
        ];
        // skip already in correct location
        yield [new SmartFileInfo(__DIR__ . '/Source/Contract/KeepThisSomeInterface.php'), null];
        // skip already in correct location
        yield [new SmartFileInfo(__DIR__ . '/Source/Contract/Foo/KeepThisSomeInterface.php'), null];
    }
}

?>
