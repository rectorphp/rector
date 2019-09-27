<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector;

use Iterator;
use Rector\Symfony\Rector\Class_\MakeCommandLazyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MakeCommandLazyRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/in_construct.php.inc'];
        yield [__DIR__ . '/Fixture/in_construct_with_param.php.inc'];
        yield [__DIR__ . '/Fixture/constant_defined_name.php.inc'];
        yield [__DIR__ . '/Fixture/set_name_fluent.php.inc'];
        yield [__DIR__ . '/Fixture/static_in_execute.php.inc'];
        yield [__DIR__ . '/Fixture/skip_non_string_param_construct.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return MakeCommandLazyRector::class;
    }
}
