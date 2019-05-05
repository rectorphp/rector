<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveDefaultArgumentValueRector;

use Rector\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDefaultArgumentValueRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/skip_previous_order.php.inc',
            __DIR__ . '/Fixture/function.php.inc',
            // reflection
            __DIR__ . '/Fixture/user_vendor_function.php.inc',
            __DIR__ . '/Fixture/system_function.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveDefaultArgumentValueRector::class;
    }
}
