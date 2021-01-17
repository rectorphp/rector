<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\Assign\ArrayAccessAddRouteToAddRouteMethodCallRector;

use Iterator;
use Rector\Nette\Rector\Assign\ArrayAccessAddRouteToAddRouteMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArrayAccessAddRouteToAddRouteMethodCallRectorTest extends AbstractRectorTestCase
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
        return ArrayAccessAddRouteToAddRouteMethodCallRector::class;
    }
}
