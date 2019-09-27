<?php declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\FuncCall\StringsAssertNakedRector;

use Iterator;
use Rector\Php72\Rector\FuncCall\StringsAssertNakedRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringsAssertNakedRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return StringsAssertNakedRector::class;
    }
}
