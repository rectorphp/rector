<?php

declare(strict_types=1);

namespace Rector\Celebrity\Tests\Rector\BooleanOp\LogicalToBooleanRector;

use Iterator;
use Rector\Celebrity\Rector\BooleanOp\LogicalToBooleanRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class LogicalToBooleanRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/or.php.inc'];
        yield [__DIR__ . '/Fixture/and.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return LogicalToBooleanRector::class;
    }
}
