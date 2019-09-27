<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfElseToTernaryRector;

use Iterator;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyIfElseToTernaryRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/keep.php.inc'];
        yield [__DIR__ . '/Fixture/too_long.php.inc'];
        yield [__DIR__ . '/Fixture/keep_nested_ternary.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return SimplifyIfElseToTernaryRector::class;
    }
}
