<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector;

use Iterator;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ExplicitBoolCompareRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/count.php.inc'];
        yield [__DIR__ . '/Fixture/array_compare.php.inc'];
        yield [__DIR__ . '/Fixture/string.php.inc'];
        yield [__DIR__ . '/Fixture/numbers.php.inc'];
        yield [__DIR__ . '/Fixture/nullable.php.inc'];
        yield [__DIR__ . '/Fixture/with_ternary.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ExplicitBoolCompareRector::class;
    }
}
