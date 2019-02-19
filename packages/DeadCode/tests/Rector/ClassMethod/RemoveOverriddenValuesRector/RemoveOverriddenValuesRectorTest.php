<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector;

use Rector\DeadCode\Rector\ClassMethod\RemoveOverriddenValuesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveOverriddenValuesRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/function.php.inc',
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/multiple_assigns.php.inc',
            __DIR__ . '/Fixture/reference_use.php.inc',
            __DIR__ . '/Fixture/method_call.php.inc',
            __DIR__ . '/Fixture/keep.php.inc',
            __DIR__ . '/Fixture/keep_pre_assign.php.inc',
            __DIR__ . '/Fixture/keep_conditional_override.php.inc',
            __DIR__ . '/Fixture/keep_re_use.php.inc',
            __DIR__ . '/Fixture/keep_re_use_2.php.inc',
            __DIR__ . '/Fixture/keep_same_level_but_different_condition_scope.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveOverriddenValuesRector::class;
    }
}
