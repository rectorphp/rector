<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector;

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
