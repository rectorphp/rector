<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Assign\NullCoalescingOperatorRector;

use Rector\Php\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NullCoalescingOperatorRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return NullCoalescingOperatorRector::class;
    }
}
