<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Ternary\UnnecessaryTernaryExpressionRector;

use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class UnnecessaryTernaryExpressionRectorTest extends AbstractRectorTestCase
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
        return UnnecessaryTernaryExpressionRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
        yield [__DIR__ . '/Fixture/fixture4.php.inc'];
    }
}
