<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector;

use Iterator;
use Rector\Autodiscovery\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector;
use Rector\Autodiscovery\Tests\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector\ValueObject\InputFilePathWithExpectedFile;
use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MoveInterfacesToContractNamespaceDirectoryRectorTest extends AbstractFileSystemRectorTestCase
{
    /**
     * @requires PHP >= 7.4
     * @dataProvider provideData()
     * @param InputFilePathWithExpectedFile[] $InputFilePathWithExpectedFiles
     */
    public function test(
        SmartFileInfo $originalFileInfo,
        string $expectedFileLocation,
        string $expectedFileContent,
        array $InputFilePathWithExpectedFiles = []
    ): void {
        $this->doTestFileInfo($originalFileInfo, $InputFilePathWithExpectedFiles);

        $this->assertFileExists($expectedFileLocation);
        $this->assertFileEquals($expectedFileContent, $expectedFileLocation);

        $this->doTestExtraFileInfos($InputFilePathWithExpectedFiles);
    }

    public function provideData(): Iterator
    {
        $extraFiles = [
            new InputFilePathWithExpectedFile(
                __DIR__ . '/Source/RandomInterfaceUseCase.php',
                $this->getFixtureTempDirectory() . '/Source/RandomInterfaceUseCase.php',
                __DIR__ . '/Expected/ExpectedRandomInterfaceUseCase.php'
            ),

            new InputFilePathWithExpectedFile(
                __DIR__ . '/Source/Entity/SameClassImplementEntity.php',
                $this->getFixtureTempDirectory() . '/Source/Entity/SameClassImplementEntity.php',
                __DIR__ . '/Expected/Entity/ExpectedSameClassImplementEntity.php'
            ),

            new InputFilePathWithExpectedFile(
                __DIR__ . '/Source/Entity/RandomInterfaceUseCaseInTheSameNamespace.php',
                $this->getFixtureTempDirectory() . '/Source/Entity/RandomInterfaceUseCaseInTheSameNamespace.php',
                __DIR__ . '/Expected/Entity/RandomInterfaceUseCaseInTheSameNamespace.php'
            ),
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Entity/RandomInterface.php'),
            $this->getFixtureTempDirectory() . '/Source/Contract/RandomInterface.php',
            __DIR__ . '/Expected/ExpectedRandomInterface.php',
            // extra files
            $extraFiles,
        ];

        // skip nette control factory
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Control/ControlFactory.php'),
            $this->getFixtureTempDirectory() . '/Source/Control/ControlFactory.php',
            __DIR__ . '/Source/Control/ControlFactory.php',
        ];

        // skip form control factory, even in docblock
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Control/FormFactory.php'),
            $this->getFixtureTempDirectory() . '/Source/Control/FormFactory.php',
            __DIR__ . '/Source/Control/FormFactory.php',
        ];

        // skip already in correct location
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Contract/KeepThisSomeInterface.php'),
            $this->getFixtureTempDirectory() . '/Source/Contract/KeepThisSomeInterface.php',
            // no change
            __DIR__ . '/Source/Contract/KeepThisSomeInterface.php',
        ];
    }

    protected function getRectorClass(): string
    {
        return MoveInterfacesToContractNamespaceDirectoryRector::class;
    }
}
