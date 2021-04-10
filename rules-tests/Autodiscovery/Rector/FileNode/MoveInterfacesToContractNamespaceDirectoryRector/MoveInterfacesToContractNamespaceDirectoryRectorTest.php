<?php

declare(strict_types=1);

namespace Rector\Tests\Autodiscovery\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector;

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class MoveInterfacesToContractNamespaceDirectoryRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.4
     * @dataProvider provideData()
     */
    public function test(
        SmartFileInfo $originalFileInfo,
        ?AddedFileWithContent $expectedAddedFileWithContent
    ): void {
        $this->doTestFileInfo($originalFileInfo);

        if ($expectedAddedFileWithContent !== null) {
            $this->assertFileWithContentWasAdded($expectedAddedFileWithContent);
        } else {
            $this->assertFileWasNotChanged($this->originalTempFileInfo);
        }
    }

    /**
     * @return Iterator<mixed>
     */
    public function provideData(): Iterator
    {
        $smartFileSystem = new SmartFileSystem();

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Entity/RandomInterface.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Source/Contract/RandomInterface.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/ExpectedRandomInterface.php')
            ),
        ];

        // skip nette control factory
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Control/ControlFactory.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Source/Control/ControlFactory.php',
                $smartFileSystem->readFile(__DIR__ . '/Source/Control/ControlFactory.php')
            ),
        ];

        // skip form control factory, even in docblock
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Control/FormFactory.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Source/Control/FormFactory.php',
                $smartFileSystem->readFile(__DIR__ . '/Source/Control/FormFactory.php')
            ),
        ];

        // skip already in correct location
        yield [new SmartFileInfo(__DIR__ . '/Source/Contract/KeepThisSomeInterface.php'), null];

        // skip already in correct location
        yield [new SmartFileInfo(__DIR__ . '/Source/Contract/Foo/KeepThisSomeInterface.php'), null];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/some_config.php';
    }
}
