<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector;

use Iterator;
use Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDoubleAssignRectorTest extends AbstractRectorTestCase
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
        return RemoveDoubleAssignRector::class;
    }
}
