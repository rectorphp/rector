<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDeadArrayKeyOverrideRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDeadArrayKeyOverrideRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): \Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return \Rector\DeadCode\Rector\Assign\RemoveDeadArrayKeyOverrideRector::class;
    }
}
