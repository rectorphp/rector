<?php declare(strict_types=1);

namespace Rector\Php52\Tests\Rector\Switch_\ContinueToBreakInSwitchRector;

use Iterator;
use Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ContinueToBreakInSwitchRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/skip_nested.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ContinueToBreakInSwitchRector::class;
    }
}
