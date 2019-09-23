<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;

final class ParamTypeDeclarationRectorTest extends AbstractRectorTestCase
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
        // @todo fix later - yield [__DIR__ . '/Fixture/aliased.php.inc'];

        yield [__DIR__ . '/Fixture/this.php.inc'];
        yield [__DIR__ . '/Fixture/complex_array.php.inc'];
        yield [__DIR__ . '/Fixture/php-cs-fixer-param/skip.php.inc'];
        yield [__DIR__ . '/Fixture/dunglas/type_aliases_and_whitelisting.php.inc'];

        yield [__DIR__ . '/Fixture/reference_symbol_in_docblock.php.inc'];
        yield [__DIR__ . '/Fixture/trait_interface.php.inc'];
        // static types
        yield [__DIR__ . '/Fixture/known_static_conflicts.php.inc'];
        // various
        yield [__DIR__ . '/Fixture/variadic.php.inc'];
        yield [__DIR__ . '/Fixture/mixed.php.inc'];
        yield [__DIR__ . '/Fixture/resource.php.inc'];
        yield [__DIR__ . '/Fixture/skip_nullable_resource.php.inc'];
        yield [__DIR__ . '/Fixture/false.php.inc'];
        yield [__DIR__ . '/Fixture/undesired.php.inc'];
        yield [__DIR__ . '/Fixture/external_scope.php.inc'];
        yield [__DIR__ . '/Fixture/local_and_external_scope.php.inc'];
        yield [__DIR__ . '/Fixture/local_scope_with_parent_interface.php.inc'];
        yield [__DIR__ . '/Fixture/local_scope_with_parent_class.php.inc'];
        yield [__DIR__ . '/Fixture/local_scope_with_parent_class2.php.inc'];

        // php cs fixer param set - - https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/4a47b6df0bf718b49269fa9920b3723d802332dc/tests/Fixer/FunctionNotation/PhpdocToParamTypeFixerTest.php
        yield [__DIR__ . '/Fixture/php-cs-fixer-param/array_native_type.php.inc'];
        yield [__DIR__ . '/Fixture/php-cs-fixer-param/array_of_types.php.inc'];

        yield [__DIR__ . '/Fixture/php-cs-fixer-param/callable_type.php.inc'];

        yield [__DIR__ . '/Fixture/php-cs-fixer-param/self_accessor.php.inc'];

        yield [__DIR__ . '/Fixture/php-cs-fixer-param/unsorted.php.inc'];

        yield [__DIR__ . '/Fixture/php-cs-fixer-param/interface.php.inc'];
        yield [__DIR__ . '/Fixture/php-cs-fixer-param/non_root_class_with_different_types_of_params.php.inc'];
        yield [__DIR__ . '/Fixture/php-cs-fixer-param/nullable.php.inc'];
        yield [__DIR__ . '/Fixture/php-cs-fixer-param/number.php.inc'];

        yield [__DIR__ . '/Fixture/php-cs-fixer-param/iterable_return_on_7_1.php.inc'];

        // nikic set - https://github.com/nikic/TypeUtil/
        yield [__DIR__ . '/Fixture/nikic/anon_class.php.inc'];
        yield [__DIR__ . '/Fixture/nikic/basic.php.inc'];
        yield [__DIR__ . '/Fixture/nikic/null.php.inc'];
        yield [__DIR__ . '/Fixture/nikic/nullable.php.inc'];
        yield [__DIR__ . '/Fixture/nikic/rename.php.inc'];

        yield [__DIR__ . '/Fixture/nikic/iterable.php.inc'];

        // dunglas set - https://github.com/dunglas/phpdoc-to-typehint/
        yield [__DIR__ . '/Fixture/dunglas/array_no_types.php.inc'];
        yield [__DIR__ . '/Fixture/dunglas/BarInterface.php.inc'];
        yield [__DIR__ . '/Fixture/dunglas/BazTrait.php.inc'];
        yield [__DIR__ . '/Fixture/dunglas/by_reference.php.inc'];
        yield [__DIR__ . '/Fixture/dunglas/Child.php.inc'];
        yield [__DIR__ . '/Fixture/dunglas/functions.php.inc'];

        yield [__DIR__ . '/Fixture/dunglas/functions2.php.inc'];
        yield [__DIR__ . '/Fixture/dunglas/param_no_type.php.inc'];
        yield [__DIR__ . '/Fixture/php-cs-fixer-param/root_class.php.inc'];
        yield [__DIR__ . '/Fixture/dunglas/nullable_types.php.inc'];

        yield [__DIR__ . '/Fixture/nikic/nullable_inheritance.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ParamTypeDeclarationRector::class;
    }
}
