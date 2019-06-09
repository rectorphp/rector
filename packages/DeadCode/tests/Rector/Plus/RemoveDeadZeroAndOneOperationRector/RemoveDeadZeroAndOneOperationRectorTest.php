<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveDeadZeroAndOneOperationRector;

use Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDeadZeroAndOneOperationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/multiple.php.inc',
            __DIR__ . '/Fixture/assigns.php.inc',
            __DIR__ . '/Fixture/skip_type_change.php.inc',
            __DIR__ . '/Fixture/skip_floats.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadZeroAndOneOperationRector::class;
    }
}
