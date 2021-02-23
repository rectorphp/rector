<?php

declare(strict_types=1);

namespace Rector\Symfony5\Tests\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RouteCollectionBuilderToRoutingConfiguratorRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfoWithoutAutoload($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return \Rector\Symfony5\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector::class;
    }
}
