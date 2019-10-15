<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;

final class ReturnTypeDeclarationRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTests()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTests(): Iterator
    {
        yield [__DIR__ . '/Fixture/PhpCsFixerReturn/various.php.inc'];
        yield [__DIR__ . '/Fixture/skip_mixed_and_string.php.inc'];
        yield [__DIR__ . '/Fixture/skip_self.php.inc'];

        // static types
        yield [__DIR__ . '/Fixture/void_type.php.inc'];
        yield [__DIR__ . '/Fixture/no_void_abstract.php.inc'];
        yield [__DIR__ . '/Fixture/code_over_doc_priority.php.inc'];
        yield [__DIR__ . '/Fixture/known_static.php.inc'];
        yield [__DIR__ . '/Fixture/known_static_void.php.inc'];
        yield [__DIR__ . '/Fixture/known_static_method.php.inc'];
        yield [__DIR__ . '/Fixture/known_static_object.php.inc'];
        yield [__DIR__ . '/Fixture/known_static_object_parent.php.inc'];
        yield [__DIR__ . '/Fixture/known_static_conflicts.php.inc'];

        yield [__DIR__ . '/Fixture/known_static_nullable.php.inc'];
        yield [__DIR__ . '/Fixture/known_static_nullable_float.php.inc'];
        yield [__DIR__ . '/Fixture/known_float.php.inc'];
        yield [__DIR__ . '/Fixture/trait_interface.php.inc'];
        yield [__DIR__ . '/Fixture/this.php.inc'];
        yield [__DIR__ . '/Fixture/false.php.inc'];
        yield [__DIR__ . '/Fixture/complex_array.php.inc'];
        yield [__DIR__ . '/Fixture/generator.php.inc'];
        yield [__DIR__ . '/Fixture/PhpCsFixerReturn/invalid_class.php.inc'];
        yield [__DIR__ . '/Fixture/PhpCsFixerReturn/invalid_return.php.inc'];
        yield [__DIR__ . '/Fixture/PhpCsFixerReturn/blacklisted_class_methods.php.inc'];
        yield [__DIR__ . '/Fixture/PhpCsFixerReturn/various_2.php.inc'];
        yield [__DIR__ . '/Fixture/PhpCsFixerReturn/arrays.php.inc'];
        yield [__DIR__ . '/Fixture/PhpCsFixerReturn/skip.php.inc'];
        yield [__DIR__ . '/Fixture/PhpCsFixerReturn/skip_union_object_object_type.php.inc'];
        yield [__DIR__ . '/Fixture/PhpCsFixerReturn/nullables.php.inc'];
        yield [__DIR__ . '/Fixture/nikic/iterable.php.inc'];
        yield [__DIR__ . '/Fixture/nikic/null.php.inc'];
        yield [__DIR__ . '/Fixture/nikic/nullable.php.inc'];
        yield [__DIR__ . '/Fixture/nikic/object.php.inc'];
        yield [__DIR__ . '/Fixture/nikic/unsupported_types.php.inc'];
        yield [__DIR__ . '/Fixture/dunglas/BarInterface.php.inc'];
        yield [__DIR__ . '/Fixture/dunglas/BazTrait.php.inc'];
        yield [__DIR__ . '/Fixture/dunglas/Child.php.inc'];
        yield [__DIR__ . '/Fixture/dunglas/nullable_types.php.inc'];
        // anonymous class
        yield [__DIR__ . '/Fixture/a_new_class.php.inc'];

        yield [__DIR__ . '/Fixture/skip_iterable_array_iterator_co_type.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ReturnTypeDeclarationRector::class;
    }
}
