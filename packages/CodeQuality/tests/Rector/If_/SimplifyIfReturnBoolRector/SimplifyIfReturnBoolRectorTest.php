<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector;

use Iterator;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyIfReturnBoolRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
        yield [__DIR__ . '/Fixture/fixture4.php.inc'];
        yield [__DIR__ . '/Fixture/fixture5.php.inc'];
        yield [__DIR__ . '/Fixture/fixture6.php.inc'];
        yield [__DIR__ . '/Fixture/fixture7.php.inc'];
        yield [__DIR__ . '/Fixture/fixture8.php.inc'];
        yield [__DIR__ . '/Fixture/fixture9.php.inc'];
        yield [__DIR__ . '/Fixture/fixture10.php.inc'];
        yield [__DIR__ . '/Fixture/should_not_remove_comments.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return SimplifyIfReturnBoolRector::class;
    }
}
