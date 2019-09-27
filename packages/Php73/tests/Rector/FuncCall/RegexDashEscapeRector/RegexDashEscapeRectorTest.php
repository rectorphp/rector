<?php declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\FuncCall\RegexDashEscapeRector;

use Iterator;
use Rector\Php73\Rector\FuncCall\RegexDashEscapeRector;
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

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/method_call.php.inc'];
        yield [__DIR__ . '/Fixture/const.php.inc'];
        yield [__DIR__ . '/Fixture/external_const.php.inc'];
        yield [__DIR__ . '/Fixture/variable.php.inc'];
        yield [__DIR__ . '/Fixture/multiple_variables.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RegexDashEscapeRector::class;
    }
}
