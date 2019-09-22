<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;

use Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddDefaultValueForUndefinedVariableRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/anonymous_function.php.inc'];
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/in_foreach.php.inc'];
        yield [__DIR__ . '/Fixture/vimeo_one.php.inc'];
        yield [__DIR__ . '/Fixture/vimeo_two.php.inc'];
        yield [__DIR__ . '/Fixture/vimeo_else.php.inc'];
        yield [__DIR__ . '/Fixture/keep_vimeo_unset.php.inc'];
        yield [__DIR__ . '/Fixture/take_static_into_account.php.inc'];
        yield [__DIR__ . '/Fixture/skip_list.php.inc'];
        yield [__DIR__ . '/Fixture/skip_foreach_assign.php.inc'];
        yield [__DIR__ . '/Fixture/skip_reference_assign.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AddDefaultValueForUndefinedVariableRector::class;
    }
}
