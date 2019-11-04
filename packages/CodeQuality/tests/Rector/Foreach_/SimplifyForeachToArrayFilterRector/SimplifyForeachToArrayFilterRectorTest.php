<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Foreach_\SimplifyForeachToArrayFilterRector;

use Iterator;
use Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyForeachToArrayFilterRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/skip.php.inc'];
        yield [__DIR__ . '/Fixture/skip_if_else.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return SimplifyForeachToArrayFilterRector::class;
    }
}
