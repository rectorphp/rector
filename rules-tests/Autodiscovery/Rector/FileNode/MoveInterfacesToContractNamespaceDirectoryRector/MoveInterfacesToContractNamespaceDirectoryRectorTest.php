<?php

declare(strict_types=1);

namespace Rector\Tests\Autodiscovery\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector;

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * @requires PHP 7.4
 */
final class MoveInterfacesToContractNamespaceDirectoryRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $originalFileInfo, AddedFileWithContent $expectedAddedFileWithContent): void
    {
        $this->doTestFileInfo($originalFileInfo);
        $this->assertFileWasAdded($expectedAddedFileWithContent);
    }

    /**
     * @return Iterator<SmartFileInfo|AddedFileWithContent[]>
     */
    public function provideData(): Iterator
    {
        $smartFileSystem = new SmartFileSystem();

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Entity/RandomInterface.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Contract/RandomInterface.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/ExpectedRandomInterface.php')
            ),
        ];
    }

    /**
     * @dataProvider provideDataSkip()
     */
    public function testSkip(SmartFileInfo $originalFileInfo): void
    {
        $this->doTestFileInfo($originalFileInfo);
        $this->assertFileWasNotChanged($this->originalTempFileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideDataSkip(): Iterator
    {
        // skip already in correct location
        yield [new SmartFileInfo(__DIR__ . '/Source/Contract/KeepThisSomeInterface.php')];

        // skip already in correct location
        yield [new SmartFileInfo(__DIR__ . '/Source/Contract/Foo/KeepThisSomeInterface.php')];

        // skip nette control factory
        yield [new SmartFileInfo(__DIR__ . '/Source/Control/ControlFactory.php')];

        // skip form control factory, even in docblock
        yield [new SmartFileInfo(__DIR__ . '/Source/Control/FormFactory.php')];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/some_config.php';
    }
}
