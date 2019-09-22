<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Property\CompleteVarDocTypePropertyRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector;

final class CompleteVarDocTypePropertyRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/property_assign.php.inc'];
        yield [__DIR__ . '/Fixture/default_value.php.inc'];
        yield [__DIR__ . '/Fixture/assign_conflict.php.inc'];
        yield [__DIR__ . '/Fixture/callable_type.php.inc'];
        yield [__DIR__ . '/Fixture/typed_array.php.inc'];
        yield [__DIR__ . '/Fixture/typed_array_nested.php.inc'];
        yield [__DIR__ . '/Fixture/symfony_console_command.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return CompleteVarDocTypePropertyRector::class;
    }
}
