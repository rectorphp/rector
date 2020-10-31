<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\Assign\NullCoalescingOperatorRector;

use Iterator;
use Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NullCoalescingOperatorRectorTest extends AbstractRectorTestCase
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
        return NullCoalescingOperatorRector::class;
    }
}
