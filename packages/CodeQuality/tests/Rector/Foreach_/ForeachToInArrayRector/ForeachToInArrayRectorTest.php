<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector;

use Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ForeachToInArrayRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
        yield [__DIR__ . '/Fixture/fixture4.php.inc'];
        yield [__DIR__ . '/Fixture/skip_collections.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ForeachToInArrayRector::class;
    }
}
