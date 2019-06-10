<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;

use Rector\Php\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddDefaultValueForUndefinedVariableRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/anonymous_function.php.inc',
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/in_foreach.php.inc',
            __DIR__ . '/Fixture/vimeo_one.php.inc',
            __DIR__ . '/Fixture/vimeo_two.php.inc',
            __DIR__ . '/Fixture/vimeo_else.php.inc',
            __DIR__ . '/Fixture/keep_vimeo_unset.php.inc',
            __DIR__ . '/Fixture/take_static_into_account.php.inc',
            // @see https://3v4l.org/ObtMn
            __DIR__ . '/Fixture/skip_list.php.inc',
            __DIR__ . '/Fixture/skip_foreach_assign.php.inc',
            __DIR__ . '/Fixture/skip_reference_assign.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return AddDefaultValueForUndefinedVariableRector::class;
    }
}
