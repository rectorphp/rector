<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;

use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedPrivateMethodRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/static_method.php.inc'];
        yield [__DIR__ . '/Fixture/private_constructor.php.inc'];
        yield [__DIR__ . '/Fixture/ignore.php.inc'];
        yield [__DIR__ . '/Fixture/keep_static_edge.php.inc'];
        yield [__DIR__ . '/Fixture/keep_anonymous.php.inc'];
        yield [__DIR__ . '/Fixture/skip_local_called.php.inc'];
        yield [__DIR__ . '/Fixture/keep_in_trait.php.inc'];
        yield [__DIR__ . '/Fixture/keep_used_method.php.inc'];
        yield [__DIR__ . '/Fixture/keep_used_method_static.php.inc'];
        yield [__DIR__ . '/Fixture/skip_anonymous_class.php.inc'];
        yield [__DIR__ . '/Fixture/skip_array_callables_this.php.inc'];
        yield [__DIR__ . '/Fixture/skip_array_callables_self.php.inc'];
        yield [__DIR__ . '/Fixture/skip_array_callables_static.php.inc'];
        yield [__DIR__ . '/Fixture/skip_array_callables_fqn.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedPrivateMethodRector::class;
    }
}
