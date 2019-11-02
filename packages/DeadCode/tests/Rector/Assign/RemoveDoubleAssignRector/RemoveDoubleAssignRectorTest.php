<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector;

use Iterator;
use Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDoubleAssignRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/self_referencing.php.inc'];
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/calls.php.inc'];
        yield [__DIR__ . '/Fixture/keep_dim_assign.php.inc'];
        yield [__DIR__ . '/Fixture/property_assign.php.inc'];
        yield [__DIR__ . '/Fixture/keep_array_reset.php.inc'];
        yield [__DIR__ . '/Fixture/keep_property_assign_in_different_ifs.php.inc'];
        yield [__DIR__ . '/Fixture/inside_if_else.php.inc'];
        yield [__DIR__ . '/Fixture/inside_the_same_if.php.inc'];
        yield [__DIR__ . '/Fixture/skip_double_catch.php.inc'];
        yield [__DIR__ . '/Fixture/skip_double_case.php.inc'];
        yield [__DIR__ . '/Fixture/skip_double_assign.php.inc'];
        yield [__DIR__ . '/Fixture/different_value.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveDoubleAssignRector::class;
    }
}
