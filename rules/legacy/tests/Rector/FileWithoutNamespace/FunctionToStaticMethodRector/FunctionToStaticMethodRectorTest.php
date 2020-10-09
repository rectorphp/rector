<?php

declare(strict_types=1);

namespace Rector\Legacy\Tests\Rector\FileWithoutNamespace\FunctionToStaticMethodRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Legacy\Rector\FileWithoutNamespace\FunctionToStaticMethodRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FunctionToStaticMethodRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $smartFileInfo): void
    {
        $this->doTestFileInfo($smartFileInfo);

        $expectedClassFilePath = $this->getFixtureTempDirectory() . '/StaticFunctions.php';
        $this->assertFileExists($expectedClassFilePath);

        $this->assertFileEquals(__DIR__ . '/Source/ExpectedStaticFunctions.php', $expectedClassFilePath);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return FunctionToStaticMethodRector::class;
    }
}
