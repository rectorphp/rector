<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Tests\Rector\Coalesce\DowngradeNullCoalescingOperatorRector;

use Iterator;
use Rector\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeNullCoalescingOperatorRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.4
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
        return DowngradeNullCoalescingOperatorRector::class;
    }
}
