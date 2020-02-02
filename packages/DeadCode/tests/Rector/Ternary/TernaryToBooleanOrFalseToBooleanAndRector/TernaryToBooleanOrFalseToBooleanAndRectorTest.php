<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector;

use Iterator;
use Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TernaryToBooleanOrFalseToBooleanAndRectorTest extends AbstractRectorTestCase
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
        return TernaryToBooleanOrFalseToBooleanAndRector::class;
    }
}
