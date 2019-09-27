<?php declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\LNumber\AddLiteralSeparatorToNumberRector;

use Iterator;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddLiteralSeparatorToNumberRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/skip_non_dec_simple_float_numbers.php.inc'];
        yield [__DIR__ . '/Fixture/skip_hexadecimal.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AddLiteralSeparatorToNumberRector::class;
    }
}
