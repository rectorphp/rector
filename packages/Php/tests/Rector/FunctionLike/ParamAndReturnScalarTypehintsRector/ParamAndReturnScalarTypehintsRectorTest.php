<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnScalarTypehintsRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Php\Rector\FunctionLike\ParamScalarTypehintRector
 * @covers \Rector\Php\Rector\FunctionLike\ReturnScalarTypehintRector
 */
final class ParamAndReturnScalarTypehintsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $integrationFiles = [
            __DIR__ . '/Fixture/this.php.inc',
            __DIR__ . '/Fixture/false.php.inc',
            __DIR__ . '/Fixture/undesired.php.inc',
            __DIR__ . '/Fixture/aliased.php.inc',
            __DIR__ . '/Fixture/external_scope.php.inc',
            __DIR__ . '/Fixture/local_and_external_scope.php.inc',
            __DIR__ . '/Fixture/local_scope_with_parent_interface.php.inc',
            __DIR__ . '/Fixture/local_scope_with_parent_class.php.inc',
            __DIR__ . '/Fixture/complex_array.php.inc',
            // php cs fixer param set - - https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/4a47b6df0bf718b49269fa9920b3723d802332dc/tests/Fixer/FunctionNotation/PhpdocToParamTypeFixerTest.php
            __DIR__ . '/Fixture/php-cs-fixer-param/array_native_type.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-param/array_of_types.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-param/callable_type.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-param/interface.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-param/iterable_return_on_7_1.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-param/non_root_class_with_different_types_of_params.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-param/nullable.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-param/number.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-param/root_class.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-param/self_accessor.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-param/skip.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-param/unsorted.php.inc',
            // php cs fixer return set - https://github.com/Slamdunk/PHP-CS-Fixer/blob/d7a409c10d0e21bc847efb26552aa65bb3c61547/tests/Fixer/FunctionNotation/PhpdocToReturnTypeFixerTest.php
            __DIR__ . '/Fixture/php-cs-fixer-return/no_doc_return.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/invalid_class.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/invalid_return.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/blacklisted_class_methods.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/various.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/various_2.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/various_3.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/self_static.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/arrays.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/skip.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/nullables.php.inc',
            // nikic set - https://github.com/nikic/TypeUtil/
            __DIR__ . '/Fixture/nikic/anon_class.php.inc',
            __DIR__ . '/Fixture/nikic/basic.php.inc',
            __DIR__ . '/Fixture/nikic/inheritance.php.inc',
            __DIR__ . '/Fixture/nikic/iterable.php.inc',
            __DIR__ . '/Fixture/nikic/name_resolution.php.inc',
            __DIR__ . '/Fixture/nikic/null.php.inc',
            __DIR__ . '/Fixture/nikic/nullable.php.inc',
            __DIR__ . '/Fixture/nikic/nullable_inheritance.php.inc',
            __DIR__ . '/Fixture/nikic/object.php.inc',
            __DIR__ . '/Fixture/nikic/rename.php.inc',
            __DIR__ . '/Fixture/nikic/return_type_position.php.inc',
            __DIR__ . '/Fixture/nikic/self_inheritance.php.inc',
            __DIR__ . '/Fixture/nikic/self_parent_static.php.inc',
            __DIR__ . '/Fixture/nikic/unsupported_types.php.inc',
            // dunglas set - https://github.com/dunglas/phpdoc-to-typehint/
            __DIR__ . '/Fixture/dunglas/array_no_types.php.inc',
            __DIR__ . '/Fixture/dunglas/BarInterface.php.inc',
            __DIR__ . '/Fixture/dunglas/BazTrait.php.inc',
            __DIR__ . '/Fixture/dunglas/by_reference.php.inc',
            __DIR__ . '/Fixture/dunglas/Child.php.inc',
            __DIR__ . '/Fixture/dunglas/Foo.php.inc',
            __DIR__ . '/Fixture/dunglas/functions.php.inc',
            __DIR__ . '/Fixture/dunglas/functions2.php.inc',
            __DIR__ . '/Fixture/dunglas/functions3.php.inc',
            __DIR__ . '/Fixture/dunglas/nullable_types.php.inc',
            __DIR__ . '/Fixture/dunglas/param_no_type.php.inc',
            __DIR__ . '/Fixture/dunglas/type_aliases_and_whitelisting.php.inc',
        ];

        $this->doTestFiles($integrationFiles);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
