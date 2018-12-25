<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector;

use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedParameterRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/order.php.inc',
            __DIR__ . '/Fixture/parent_required.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedParameterRector::class;
    }
}
