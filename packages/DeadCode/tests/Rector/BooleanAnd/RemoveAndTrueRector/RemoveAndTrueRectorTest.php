<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\BooleanAnd\RemoveAndTrueRector;

use Iterator;
use Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveAndTrueRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/keep_something.php.inc'];
        yield [__DIR__ . '/Fixture/keep_property_changed_in_another_method.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveAndTrueRector::class;
    }
}
