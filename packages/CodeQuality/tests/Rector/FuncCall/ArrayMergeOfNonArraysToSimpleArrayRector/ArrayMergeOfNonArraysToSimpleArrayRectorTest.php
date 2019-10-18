<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector;

use Iterator;
use Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ArrayMergeOfNonArraysToSimpleArrayRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        // https://3v4l.org/2r26K
        yield [__DIR__ . '/Fixture/nested_arrays.php.inc'];
        // skip multiple items https://3v4l.org/anks3
        yield [__DIR__ . '/Fixture/skip_non_arrays.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ArrayMergeOfNonArraysToSimpleArrayRector::class;
    }
}
