<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\If_\SimplifyIfElseWithSameContentRector;

use Iterator;
use Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyIfElseWithSameContentRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/with_else_ifs.php.inc'];
        yield [__DIR__ . '/Fixture/skip_different_content.php.inc'];
        yield [__DIR__ . '/Fixture/skip_missing_else.php.inc'];
        yield [__DIR__ . '/Fixture/skip_else_with_no_return.php.inc'];
        yield [__DIR__ . '/Fixture/skip_more_than_return.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return SimplifyIfElseWithSameContentRector::class;
    }
}
