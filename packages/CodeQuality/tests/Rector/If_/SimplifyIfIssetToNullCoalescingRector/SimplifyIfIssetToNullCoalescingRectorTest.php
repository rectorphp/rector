<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfIssetToNullCoalescingRector;

use Rector\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyIfIssetToNullCoalescingRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return SimplifyIfIssetToNullCoalescingRector::class;
    }
}
