<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\RegexDashEscapeRector;

use Rector\Php\Rector\FuncCall\RegexDashEscapeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RegexDashEscapeRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/method_call.php.inc'];
        yield [__DIR__ . '/Fixture/const.php.inc'];
        yield [__DIR__ . '/Fixture/variable.php.inc'];
        yield [__DIR__ . '/Fixture/multiple_variables.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RegexDashEscapeRector::class;
    }
}
