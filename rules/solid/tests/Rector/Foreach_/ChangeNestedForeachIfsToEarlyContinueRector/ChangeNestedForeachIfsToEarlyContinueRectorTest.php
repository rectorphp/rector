<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\SOLID\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;

final class ChangeNestedForeachIfsToEarlyContinueRectorTest extends AbstractRectorTestCase
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
        return ChangeNestedForeachIfsToEarlyContinueRector::class;
    }
}
