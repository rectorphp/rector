<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector;

use Iterator;
use Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Tests copied from:
 * - https://github.com/slevomat/coding-standard/blob/9978172758e90bc2355573e0b5d99062d87b14a3/tests/Sniffs/Variables/data/uselessVariableErrors.fixed.php
 * - https://github.com/slevomat/coding-standard/blob/9978172758e90bc2355573e0b5d99062d87b14a3/tests/Sniffs/Variables/data/uselessVariableNoErrors.php
 */
final class SimplifyUselessVariableRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
        yield [__DIR__ . '/Fixture/in_a_function.php.inc'];
        yield [__DIR__ . '/Fixture/keep_visual.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return SimplifyUselessVariableRector::class;
    }
}
