<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedParamTypeRector;

final class AddMethodCallBasedParamTypeRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/second_position.php.inc'];
        yield [__DIR__ . '/Fixture/static_call.php.inc'];
        yield [__DIR__ . '/Fixture/assigned_variable.php.inc'];
        yield [__DIR__ . '/Fixture/instant_class_call.php.inc'];
        yield [__DIR__ . '/Fixture/unioned_double_calls.php.inc'];
        yield [__DIR__ . '/Fixture/override.php.inc'];
        yield [__DIR__ . '/Fixture/skip_already_completed.php.inc'];
        yield [__DIR__ . '/Fixture/skip_mixed.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AddMethodCallBasedParamTypeRector::class;
    }
}
