<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Switch_\BinarySwitchToIfElseRector;

use Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class BinarySwitchToIfElseRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function getRectorClass(): string
    {
        return BinarySwitchToIfElseRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/in_class.php.inc'];
        yield [__DIR__ . '/Fixture/if_or.php.inc'];
        yield [__DIR__ . '/Fixture/extra_break.php.inc'];
    }
}
