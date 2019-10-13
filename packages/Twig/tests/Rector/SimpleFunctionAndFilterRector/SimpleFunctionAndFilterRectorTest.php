<?php

declare(strict_types=1);

namespace Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Twig\Rector\SimpleFunctionAndFilterRector;

final class SimpleFunctionAndFilterRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return SimpleFunctionAndFilterRector::class;
    }
}
