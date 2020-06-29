<?php

declare(strict_types=1);

namespace Rector\Sensio\Tests\Rector\ClassMethod\RemoveServiceFromSensioRouteRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Sensio\Rector\ClassMethod\RemoveServiceFromSensioRouteRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveServiceFromSensioRouteRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return RemoveServiceFromSensioRouteRector::class;
    }
}
