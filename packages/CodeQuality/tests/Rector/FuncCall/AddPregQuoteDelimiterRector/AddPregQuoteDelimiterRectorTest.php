<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\AddPregQuoteDelimiterRector;

use Iterator;
use Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddPregQuoteDelimiterRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return AddPregQuoteDelimiterRector::class;
    }
}
