<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Foreach_\RemoveUnusedForeachKeyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;

final class RemoveUnusedForeachKeyRectorTest extends AbstractRectorTestCase
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
        return RemoveUnusedForeachKeyRector::class;
    }
}
