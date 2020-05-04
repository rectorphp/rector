<?php

declare(strict_types=1);

namespace Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Order\Rector\Class_\OrderPropertyByComplexityRector;

final class OrderPropertyByComplexityRectorTest extends AbstractRectorTestCase
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
        return OrderPropertyByComplexityRector::class;
    }
}
