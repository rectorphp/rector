<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector;

use Rector\Php\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class BreakNotInLoopOrSwitchToReturnRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFilesWithoutAutoload([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/foreach_not.php.inc',
            __DIR__ . '/Fixture/return.php.inc',
            __DIR__ . '/Fixture/Keep.php',
        ]);
    }

    protected function getRectorClass(): string
    {
        return BreakNotInLoopOrSwitchToReturnRector::class;
    }
}
