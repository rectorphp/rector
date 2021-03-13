<?php

declare(strict_types=1);

namespace Rector\Tests\Php54\Rector\Break_\RemoveZeroBreakContinueRector;

use Iterator;
use Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveZeroBreakContinueRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        // to prevent loading PHP 5.4+ invalid code
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return RemoveZeroBreakContinueRector::class;
    }
}
