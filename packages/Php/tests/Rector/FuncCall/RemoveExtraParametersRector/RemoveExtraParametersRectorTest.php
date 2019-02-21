<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector;

use Rector\Php\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveExtraParametersRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/func_get_all.php.inc',
            __DIR__ . '/Fixture/better_func_get_all.php.inc',
            __DIR__ . '/Fixture/methods.php.inc',
            __DIR__ . '/Fixture/static_calls.php.inc',
            __DIR__ . '/Fixture/external_scope.php.inc',
            __DIR__ . '/Fixture/static_call_parent.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveExtraParametersRector::class;
    }
}
