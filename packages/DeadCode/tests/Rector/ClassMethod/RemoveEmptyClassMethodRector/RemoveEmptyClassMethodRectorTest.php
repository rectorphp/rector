<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector;

use Iterator;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveEmptyClassMethodRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/simple.php.inc'];
        yield [__DIR__ . '/Fixture/with_parent.php.inc'];
        yield [__DIR__ . '/Fixture/with_interface.php.inc'];
        yield [__DIR__ . '/Fixture/keep_abstract_method.php.inc'];
        yield [__DIR__ . '/Fixture/keep_protected_child_method.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveEmptyClassMethodRector::class;
    }
}
