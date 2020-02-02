<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector;

use Iterator;
use Rector\DeadCode\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveSetterOnlyPropertyAndMethodCallRectorTest extends AbstractRectorTestCase
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
        return RemoveSetterOnlyPropertyAndMethodCallRector::class;
    }
}
