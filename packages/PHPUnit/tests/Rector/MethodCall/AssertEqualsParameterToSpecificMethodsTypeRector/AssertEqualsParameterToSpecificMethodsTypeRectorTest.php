<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector;

use Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertEqualsParameterToSpecificMethodsTypeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/remove_max_depth.php.inc',
            __DIR__ . '/Fixture/refactor_canonize.php.inc',
            __DIR__ . '/Fixture/refactor_delta.php.inc',
            __DIR__ . '/Fixture/refactor_ignore_case.php.inc',
            // all together
            __DIR__ . '/Fixture/combination.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return AssertEqualsParameterToSpecificMethodsTypeRector::class;
    }
}
