<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveDefaultArgumentValueRector;

use Rector\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDefaultArgumentValueRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/skip_previous_order.php.inc'];
        yield [__DIR__ . '/Fixture/function.php.inc'];
        yield [__DIR__ . '/Fixture/user_vendor_function.php.inc'];
        yield [__DIR__ . '/Fixture/system_function.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveDefaultArgumentValueRector::class;
    }
}
