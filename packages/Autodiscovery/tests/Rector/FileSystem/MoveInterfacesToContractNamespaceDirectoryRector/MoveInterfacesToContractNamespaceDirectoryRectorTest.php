<?php declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector;

use Iterator;
use Nette\Utils\FileSystem;
use Rector\Autodiscovery\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector;
use Rector\Testing\PHPUnit\AbstractFileSystemRectorTestCase;

final class MoveInterfacesToContractNamespaceDirectoryRectorTest extends AbstractFileSystemRectorTestCase
{
    protected function tearDown(): void
    {
        FileSystem::delete(__DIR__ . '/Source/Fixture');
    }

    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $originalFile, string $expectedFileLocation, string $expectedFileContent): void
    {
        $this->doTestFile($originalFile);

        $this->assertFileExists($expectedFileLocation);
        $this->assertFileEquals($expectedFileContent, $expectedFileLocation);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): Iterator
    {
        yield [
            __DIR__ . '/Source/Entity/RandomInterface.php',
            __DIR__ . '/Source/Fixture/Contract/RandomInterface.php',
            __DIR__ . '/Fixture/ExpectedRandomInterface.php',
        ];
    }

    protected function getRectorClass(): string
    {
        return MoveInterfacesToContractNamespaceDirectoryRector::class;
    }
}
