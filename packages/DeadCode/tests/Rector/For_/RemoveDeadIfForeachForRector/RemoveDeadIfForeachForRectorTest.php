<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\For_\RemoveDeadIfForeachForRector;

use Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDeadIfForeachForRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/side_effect_checks.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadIfForeachForRector::class;
    }
}
