<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Closure\ClosureToArrowFunctionRector;

use Rector\Php\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ClosureToArrowFunctionRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/referenced_but_not_used.php.inc'];
        yield [__DIR__ . '/Fixture/skip_no_return.php.inc'];
        yield [__DIR__ . '/Fixture/skip_referenced_value.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ClosureToArrowFunctionRector::class;
    }
}
