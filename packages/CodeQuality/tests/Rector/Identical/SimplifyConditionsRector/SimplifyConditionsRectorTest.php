<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyConditionsRector;

use Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyConditionsRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/boolean_not.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
        yield [__DIR__ . '/Fixture/dual_null.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return SimplifyConditionsRector::class;
    }
}
