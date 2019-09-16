<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector;

use Rector\CodeQuality\Rector\For_\ForToForeachRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ForToForeachRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/for_with_count.php.inc'];
        yield [__DIR__ . '/Fixture/for_with_switched_compare.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ForToForeachRector::class;
    }
}
