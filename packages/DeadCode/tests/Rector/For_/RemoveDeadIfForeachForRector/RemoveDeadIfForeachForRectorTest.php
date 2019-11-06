<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\For_\RemoveDeadIfForeachForRector;

use Iterator;
use Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDeadIfForeachForRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadIfForeachForRector::class;
    }
}
