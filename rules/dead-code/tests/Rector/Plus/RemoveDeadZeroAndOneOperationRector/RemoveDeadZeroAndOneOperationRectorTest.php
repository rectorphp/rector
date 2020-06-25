<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveDeadZeroAndOneOperationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveDeadZeroAndOneOperationRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadZeroAndOneOperationRector::class;
    }
}
