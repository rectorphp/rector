<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyBoolIdenticalTrueRector;

use Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyBoolIdenticalTrueRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/directly.php.inc'];
        yield [__DIR__ . '/Fixture/negate.php.inc'];
        yield [__DIR__ . '/Fixture/double_negate.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return SimplifyBoolIdenticalTrueRector::class;
    }
}
