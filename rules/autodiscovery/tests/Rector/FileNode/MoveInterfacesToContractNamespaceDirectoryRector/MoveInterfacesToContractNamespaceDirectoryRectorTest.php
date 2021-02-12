<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector;

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Testing\ValueObject\InputFilePathWithExpectedFile;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class MoveInterfacesToContractNamespaceDirectoryRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.4
     * @dataProvider provideData()
     * @param InputFilePathWithExpectedFile[] $extraFiles
     */
    public function test(
        SmartFileInfo $originalFileInfo,
        ?AddedFileWithContent $expectedAddedFileWithContent,
        array $extraFiles = []
    ): void {
        $this->doTestFileInfo($originalFileInfo, $extraFiles);

        if ($expectedAddedFileWithContent !== null) {
            $this->assertFileWithContentWasAdded($expectedAddedFileWithContent);
        } else {
            $this->assertFileWasNotChanged($this->originalTempFileInfo);
        }

        $expectedAddedFilesWithContent = [];
        foreach ($extraFiles as $extraFile) {
            $expectedAddedFilesWithContent[] = $extraFile->getAddedFileWithContent();
        }

        $this->assertFilesWereAdded($expectedAddedFilesWithContent);
    }

    public function provideData(): Iterator
    {
        $smartFileSystem = new SmartFileSystem();

        $extraFiles = [
            new InputFilePathWithExpectedFile(
                __DIR__ . '/Source/RandomInterfaceUseCase.php',
                new AddedFileWithContent(
                    $this->getFixtureTempDirectory() . '/Source/RandomInterfaceUseCase.php',
                    $smartFileSystem->readFile(__DIR__ . '/Expected/ExpectedRandomInterfaceUseCase.php')
                )
            ),

            new InputFilePathWithExpectedFile(
                __DIR__ . '/Source/ValueObject/SameClassImplementEntity.php',
                new AddedFileWithContent(
                    $this->getFixtureTempDirectory() . '/Source/Entity/SameClassImplementEntity.php',
                    $smartFileSystem->readFile(__DIR__ . '/Expected/Entity/ExpectedSameClassImplementEntity.php')
                )
            ),

            new InputFilePathWithExpectedFile(
                __DIR__ . '/Source/Entity/RandomInterfaceUseCaseInTheSameNamespace.php',
                new AddedFileWithContent(
                    $this->getFixtureTempDirectory() . '/Source/Entity/RandomInterfaceUseCaseInTheSameNamespace.php',
                    $smartFileSystem->readFile(
                        __DIR__ . '/Expected/Entity/RandomInterfaceUseCaseInTheSameNamespace.php'
                    )
                )
            ),
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Entity/RandomInterface.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Source/Contract/RandomInterface.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/ExpectedRandomInterface.php')
            ),
            // extra files
            $extraFiles,
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

    protected function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/some_config.php';
    }
}
