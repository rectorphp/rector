<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\List_\ListSwapArrayOrderRector;

use Rector\Php\Rector\List_\ListSwapArrayOrderRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ListSwapArrayOrderRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function getRectorClass(): string
    {
        return ListSwapArrayOrderRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/skip.php.inc'];
        yield [__DIR__ . '/Fixture/skip_empty.php.inc'];
    }
}
