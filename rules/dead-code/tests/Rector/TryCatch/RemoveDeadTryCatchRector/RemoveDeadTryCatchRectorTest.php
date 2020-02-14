<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\TryCatch\RemoveDeadTryCatchRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector;

final class RemoveDeadTryCatchRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadTryCatchRector::class;
    }
}
