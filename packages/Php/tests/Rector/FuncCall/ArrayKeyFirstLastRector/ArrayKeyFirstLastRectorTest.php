<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\ArrayKeyFirstLastRector;

use Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ArrayKeyFirstLastRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/array_key_first.php.inc'];
        yield [__DIR__ . '/Fixture/array_key_last.php.inc'];
        yield [__DIR__ . '/Fixture/both.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ArrayKeyFirstLastRector::class;
    }
}
