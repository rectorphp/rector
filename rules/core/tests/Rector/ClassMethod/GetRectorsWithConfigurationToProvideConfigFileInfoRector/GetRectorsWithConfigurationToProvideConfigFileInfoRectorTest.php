<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\ClassMethod\GetRectorsWithConfigurationToProvideConfigFileInfoRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class GetRectorsWithConfigurationToProvideConfigFileInfoRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return \Rector\Core\Rector\ClassMethod\GetRectorsWithConfigurationToProvideConfigFileInfoRector::class;
    }
}
