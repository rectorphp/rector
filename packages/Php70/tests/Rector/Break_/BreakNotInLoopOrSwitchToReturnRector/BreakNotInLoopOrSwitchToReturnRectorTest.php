<?php declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector;

use Iterator;
use Rector\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class BreakNotInLoopOrSwitchToReturnRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFileWithoutAutoload($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/foreach_not.php.inc'];
        yield [__DIR__ . '/Fixture/return.php.inc'];
        yield [__DIR__ . '/Fixture/Keep.php'];
    }

    protected function getRectorClass(): string
    {
        return BreakNotInLoopOrSwitchToReturnRector::class;
    }
}
