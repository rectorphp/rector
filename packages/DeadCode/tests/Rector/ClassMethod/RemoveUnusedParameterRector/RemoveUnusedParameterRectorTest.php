<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector;

use Iterator;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedParameterRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/change_when_not_used_in_children.php.inc'];
        yield [__DIR__ . '/Fixture/dont_change_parent.php.inc'];
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/order.php.inc'];
        yield [__DIR__ . '/Fixture/parent_required.php.inc'];
        yield [__DIR__ . '/Fixture/in_between_parameter.php.inc'];
        yield [__DIR__ . '/Fixture/compact.php.inc'];
        yield [__DIR__ . '/Fixture/keep_magic_methods_param.php.inc'];
        yield [__DIR__ . '/Fixture/keep_anonymous_classes.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedParameterRector::class;
    }
}
