<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\ConsistentImplodeRector;

use Iterator;
use Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ConsistentImplodeRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/skip_already_switched.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ConsistentImplodeRector::class;
    }
}
