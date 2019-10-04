<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\ShortenElseIfRector;

use Iterator;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ShortenElseIfRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/simple.php.inc'];
        yield [__DIR__ . '/Fixture/nested-else.php.inc'];
        yield [__DIR__ . '/Fixture/nested-elseif.php.inc'];
        yield [__DIR__ . '/Fixture/recursive.php.inc'];
        yield [__DIR__ . '/Fixture/skip-no-else-if.php.inc'];
        yield [__DIR__ . '/Fixture/skip-multiple-stmts.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ShortenElseIfRector::class;
    }
}
