<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;

final class AddArrayReturnDocTypeRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/setter_based.php.inc'];
        yield [__DIR__ . '/Fixture/simple_array.php.inc'];
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fully_qualified_name.php.inc'];
        yield [__DIR__ . '/Fixture/fully_qualified_name_nested_array.php.inc'];
        yield [__DIR__ . '/Fixture/yield_strings.php.inc'];
        yield [__DIR__ . '/Fixture/add_without_return_type_declaration.php.inc'];
        yield [__DIR__ . '/Fixture/fix_incorrect_array.php.inc'];
        yield [__DIR__ . '/Fixture/return_uuid.php.inc'];

        // skip
        yield [__DIR__ . '/Fixture/skip_shorten_class_name.php.inc'];
        yield [__DIR__ . '/Fixture/skip_constructor.php.inc'];
        yield [__DIR__ . '/Fixture/skip_inner_function_return.php.inc'];
        yield [__DIR__ . '/Fixture/skip_array_after_array_type.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AddArrayReturnDocTypeRector::class;
    }
}
