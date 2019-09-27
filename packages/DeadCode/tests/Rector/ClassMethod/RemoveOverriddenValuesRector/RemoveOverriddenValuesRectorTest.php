<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector;

use Iterator;
use Rector\DeadCode\Rector\ClassMethod\RemoveOverriddenValuesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveOverriddenValuesRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/function.php.inc'];
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/multiple_assigns.php.inc'];
        yield [__DIR__ . '/Fixture/reference_use.php.inc'];
        yield [__DIR__ . '/Fixture/method_call.php.inc'];
        yield [__DIR__ . '/Fixture/keep.php.inc'];
        yield [__DIR__ . '/Fixture/keep_pre_assign.php.inc'];
        yield [__DIR__ . '/Fixture/keep_conditional_override.php.inc'];
        yield [__DIR__ . '/Fixture/keep_re_use.php.inc'];
        yield [__DIR__ . '/Fixture/keep_re_use_2.php.inc'];
        yield [__DIR__ . '/Fixture/keep_same_level_but_different_condition_scope.php.inc'];
        yield [__DIR__ . '/Fixture/issue_1093.php.inc'];
        yield [__DIR__ . '/Fixture/issue_1286.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveOverriddenValuesRector::class;
    }
}
