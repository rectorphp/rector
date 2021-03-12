<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\Assign\ListSwapArrayOrderRector;

use Iterator;
use Rector\Php70\Rector\Assign\ListSwapArrayOrderRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ListSwapArrayOrderRectorTest extends AbstractRectorTestCase
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
        return ListSwapArrayOrderRector::class;
    }
}
