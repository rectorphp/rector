<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\StringifyDefineRector;

use Rector\Php\Rector\FuncCall\StringifyDefineRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringifyDefineRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return StringifyDefineRector::class;
    }
}
