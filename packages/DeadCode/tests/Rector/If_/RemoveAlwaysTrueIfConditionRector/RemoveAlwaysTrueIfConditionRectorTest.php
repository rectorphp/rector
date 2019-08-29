<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\If_\RemoveAlwaysTrueIfConditionRector;

use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveAlwaysTrueIfConditionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RemoveAlwaysTrueIfConditionRector::class;
    }
}
