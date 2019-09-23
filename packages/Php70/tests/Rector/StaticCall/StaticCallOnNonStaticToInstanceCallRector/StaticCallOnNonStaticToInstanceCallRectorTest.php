<?php declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector;

use Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StaticCallOnNonStaticToInstanceCallRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/with_constructor.php.inc'];
        yield [__DIR__ . '/Fixture/keep.php.inc'];
        yield [__DIR__ . '/Fixture/keep_parent_static.php.inc'];
        yield [__DIR__ . '/Fixture/keep_annotated.php.inc'];
        yield [__DIR__ . '/Fixture/add_static_to_method.php.inc'];
        yield [__DIR__ . '/Fixture/with_only_static_methods.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return StaticCallOnNonStaticToInstanceCallRector::class;
    }
}
