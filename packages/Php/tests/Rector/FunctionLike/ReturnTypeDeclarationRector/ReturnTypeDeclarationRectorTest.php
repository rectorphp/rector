<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector;

use Rector\Php\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReturnTypeDeclarationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $integrationFiles = [
            // static types
            __DIR__ . '/Fixture/void_type.php.inc',
            __DIR__ . '/Fixture/no_void_abstract.php.inc',
            __DIR__ . '/Fixture/code_over_doc_priority.php.inc',
            __DIR__ . '/Fixture/known_static.php.inc',
            __DIR__ . '/Fixture/known_static_conflicts.php.inc',
            __DIR__ . '/Fixture/known_static_method.php.inc',
            __DIR__ . '/Fixture/known_static_object.php.inc',
            __DIR__ . '/Fixture/known_static_object_parent.php.inc',
            // various
            __DIR__ . '/Fixture/known_static_nullable.php.inc',
            __DIR__ . '/Fixture/known_float.php.inc',
            __DIR__ . '/Fixture/trait_interface.php.inc',
            __DIR__ . '/Fixture/this.php.inc',
            __DIR__ . '/Fixture/false.php.inc',
            __DIR__ . '/Fixture/complex_array.php.inc',
            // php cs fixer return set - https://github.com/Slamdunk/PHP-CS-Fixer/blob/d7a409c10d0e21bc847efb26552aa65bb3c61547/tests/Fixer/FunctionNotation/PhpdocToReturnTypeFixerTest.php
            __DIR__ . '/Fixture/php-cs-fixer-return/invalid_class.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/invalid_return.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/blacklisted_class_methods.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/various.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/various_2.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/self_static.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/arrays.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/skip.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-return/nullables.php.inc',
            // nikic set - https://github.com/nikic/TypeUtil/
            __DIR__ . '/Fixture/nikic/inheritance.php.inc',
            __DIR__ . '/Fixture/nikic/iterable.php.inc',
            __DIR__ . '/Fixture/nikic/name_resolution.php.inc',
            __DIR__ . '/Fixture/nikic/null.php.inc',
            __DIR__ . '/Fixture/nikic/nullable.php.inc',
            __DIR__ . '/Fixture/nikic/nullable_inheritance.php.inc',
            __DIR__ . '/Fixture/nikic/object.php.inc',
            __DIR__ . '/Fixture/nikic/return_type_position.php.inc',
            __DIR__ . '/Fixture/nikic/self_inheritance.php.inc',
            __DIR__ . '/Fixture/nikic/self_parent_static.php.inc',
            __DIR__ . '/Fixture/nikic/unsupported_types.php.inc',
            // dunglas set - https://github.com/dunglas/phpdoc-to-typehint/
            __DIR__ . '/Fixture/dunglas/BarInterface.php.inc',
            __DIR__ . '/Fixture/dunglas/BazTrait.php.inc',
            __DIR__ . '/Fixture/dunglas/Child.php.inc',
            __DIR__ . '/Fixture/dunglas/nullable_types.php.inc',
        ];

        $this->doTestFiles($integrationFiles);
    }

    protected function getRectorClass(): string
    {
        return ReturnTypeDeclarationRector::class;
    }
}
