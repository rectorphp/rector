<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector;

use Iterator;
use Rector\DeadCode\Rector\Class_\RemoveUnusedClassesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedClassesRectorTest extends AbstractRectorTestCase
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
        return RemoveUnusedClassesRector::class;
    }
}
