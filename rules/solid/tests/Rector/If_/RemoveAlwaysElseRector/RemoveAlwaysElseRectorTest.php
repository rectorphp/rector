<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElseRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\SOLID\Rector\If_\RemoveAlwaysElseRector;

final class RemoveAlwaysElseRectorTest extends AbstractRectorTestCase
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
        return RemoveAlwaysElseRector::class;
    }
}
