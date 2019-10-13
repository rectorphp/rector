<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\String_\SymplifyQuoteEscapeRector;

use Iterator;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SymplifyQuoteEscapeRectorTest extends AbstractRectorTestCase
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
        return SymplifyQuoteEscapeRector::class;
    }
}
