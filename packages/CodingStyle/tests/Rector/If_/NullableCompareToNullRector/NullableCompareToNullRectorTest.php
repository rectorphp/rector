<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\If_\NullableCompareToNullRector;

use Iterator;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NullableCompareToNullRectorTest extends AbstractRectorTestCase
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
        return NullableCompareToNullRector::class;
    }
}
