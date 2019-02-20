<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Switch_\ContinueToBreakInSwitchRector;

use Rector\Php\Rector\Switch_\ContinueToBreakInSwitchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ContinueToBreakInSwitchRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFilesWithoutAutoload([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/skip_nested.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ContinueToBreakInSwitchRector::class;
    }
}
