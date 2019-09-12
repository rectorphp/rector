<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\ParseStrWithResultArgumentRector;

use Rector\Php\Rector\FuncCall\ParseStrWithResultArgumentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ParseStrWithResultArgumentRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/skip_already_set.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ParseStrWithResultArgumentRector::class;
    }
}
