<?php declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\Unset_\UnsetCastRector;

use Rector\Php72\Rector\Unset_\UnsetCastRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class UnsetCastRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/unset_expr.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return UnsetCastRector::class;
    }
}
