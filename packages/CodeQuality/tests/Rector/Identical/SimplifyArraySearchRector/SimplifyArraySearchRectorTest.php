<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyArraySearchRector;

use Iterator;
use Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyArraySearchRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return SimplifyArraySearchRector::class;
    }
}
