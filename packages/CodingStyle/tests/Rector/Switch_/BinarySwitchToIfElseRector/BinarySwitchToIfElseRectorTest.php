<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Switch_\BinarySwitchToIfElseRector;

use Iterator;
use Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class BinarySwitchToIfElseRectorTest extends AbstractRectorTestCase
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
        return BinarySwitchToIfElseRector::class;
    }
}
