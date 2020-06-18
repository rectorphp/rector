<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector;

use Iterator;
use Rector\Autodiscovery\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector;
use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;

final class MoveInterfacesToContractNamespaceDirectoryRectorTest extends AbstractFileSystemRectorTestCase
{
    /**
     * @dataProvider provideData()
     * @param string[] $extraFiles
     */
    public function test(
        string $originalFile,
        string $expectedFileLocation,
        string $expectedFileContent,
        array $extraFiles = [],
        ?string $extraExpectedFileLocation = null,
        ?string $expectedExtraFileContent = null
    ): void {
        $this->doTestFile($originalFile, $extraFiles);

        $this->assertFileExists($expectedFileLocation);
        $this->assertFileEquals($expectedFileContent, $expectedFileLocation);

        if ($extraExpectedFileLocation !== null) {
            $this->assertFileExists($extraExpectedFileLocation);

            if ($expectedExtraFileContent !== null) {
                $this->assertFileEquals($expectedExtraFileContent, $extraExpectedFileLocation);
            }
        }
    }

    public function provideData(): Iterator
    {
        yield [
            __DIR__ . '/Source/Entity/RandomInterface.php',
            $this->getFixtureTempDirectory() . '/Source/Contract/RandomInterface.php',
            __DIR__ . '/Expected/ExpectedRandomInterface.php',
            // extra file
            [__DIR__ . '/Source/RandomInterfaceUseCase.php'],
            $this->getFixtureTempDirectory() . '/Source/RandomInterfaceUseCase.php',
            __DIR__ . '/Expected/ExpectedRandomInterfaceUseCase.php',
        ];

        // skip nette control factory
        yield [
            __DIR__ . '/Source/Control/ControlFactory.php',
            $this->getFixtureTempDirectory() . '/Source/Control/ControlFactory.php',
            __DIR__ . '/Source/Control/ControlFactory.php',
        ];

        // skip form control factory, even in docblock
        yield [
            __DIR__ . '/Source/Control/FormFactory.php',
            $this->getFixtureTempDirectory() . '/Source/Control/FormFactory.php',
            __DIR__ . '/Source/Control/FormFactory.php',
        ];

        // skip already in correct location
        yield [
            __DIR__ . '/Source/Contract/KeepThisSomeInterface.php',
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
