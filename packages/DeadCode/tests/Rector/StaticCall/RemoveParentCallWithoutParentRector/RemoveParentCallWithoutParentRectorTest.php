<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\StaticCall\RemoveParentCallWithoutParentRector;

use Iterator;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveParentCallWithoutParentRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/parent_but_no_method.php.inc'];
        yield [__DIR__ . '/Fixture/skip_trait.php.inc'];
        yield [__DIR__ . '/Fixture/edge_case.php.inc'];
        yield [__DIR__ . '/Fixture/anonymous_class_without_parent.php.inc'];
        yield [__DIR__ . '/Fixture/anonymous_class_with_parent.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveParentCallWithoutParentRector::class;
    }
}
