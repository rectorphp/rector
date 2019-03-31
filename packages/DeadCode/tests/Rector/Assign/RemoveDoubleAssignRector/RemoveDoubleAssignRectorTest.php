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
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveDoubleAssignRector::class;
    }
}
