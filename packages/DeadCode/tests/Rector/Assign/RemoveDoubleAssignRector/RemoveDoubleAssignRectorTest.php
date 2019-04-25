<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector;

use Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDoubleAssignRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/calls.php.inc',
            __DIR__ . '/Fixture/keep_dim_assign.php.inc',
            __DIR__ . '/Fixture/property_assign.php.inc',
            __DIR__ . '/Fixture/keep_array_reset.php.inc',
            __DIR__ . '/Fixture/keep_property_assign_in_different_ifs.php.inc',
            __DIR__ . '/Fixture/inside_if_else.php.inc',
            __DIR__ . '/Fixture/inside_the_same_if.php.inc',
            // skip
            __DIR__ . '/Fixture/skip_double_catch.php.inc',
            __DIR__ . '/Fixture/skip_double_case.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveDoubleAssignRector::class;
    }
}
