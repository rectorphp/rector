<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Foreach_\SimplifyForeachToCoalescingRector;

use Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyForeachToCoalescingRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/foreach_key_value.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return SimplifyForeachToCoalescingRector::class;
    }
}
