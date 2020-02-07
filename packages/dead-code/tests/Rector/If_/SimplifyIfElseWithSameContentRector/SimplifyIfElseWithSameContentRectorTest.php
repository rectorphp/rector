<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\If_\SimplifyIfElseWithSameContentRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector;

final class SimplifyIfElseWithSameContentRectorTest extends AbstractRectorTestCase
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
        return SimplifyIfElseWithSameContentRector::class;
    }
}
