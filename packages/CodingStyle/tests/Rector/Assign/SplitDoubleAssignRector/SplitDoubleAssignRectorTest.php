<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Assign\SplitDoubleAssignRector;

use Iterator;
use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SplitDoubleAssignRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return SplitDoubleAssignRector::class;
    }
}
