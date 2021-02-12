<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector;

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class MoveValueObjectsToValueObjectDirectoryRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fixtureFileInfo, ?AddedFileWithContent $expectedAddedFileWithContent): void
    {
        $this->doTestFileInfo($fixtureFileInfo);

        if ($expectedAddedFileWithContent !== null) {
            $this->assertFileWithContentWasAdded($expectedAddedFileWithContent);
        } else {
            $this->assertFileWasNotChanged($this->originalTempFileInfo);
        }
    }

    public function provideData(): Iterator
    {
        $smartFileSystem = new SmartFileSystem();

        yield [
            new SmartFileInfo(__DIR__ . '/Source/Repository/PrimitiveValueObject.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Source/ValueObject/PrimitiveValueObject.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/ValueObject/PrimitiveValueObject.php')
            ),
        ];

        // type
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Command/SomeName.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Source/ValueObject/SomeName.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/ValueObject/SomeName.php')
            ),
        ];

        // suffix
        yield [
            new SmartFileInfo(__DIR__ . '/Source/Command/MeSearch.php'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/Source/ValueObject/MeSearch.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/ValueObject/MeSearch.php')
            ),
        ];

        // skip known service types
        yield [new SmartFileInfo(__DIR__ . '/Source/Utils/SomeSuffixedTest.php.inc'), null];
    }

    protected function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
