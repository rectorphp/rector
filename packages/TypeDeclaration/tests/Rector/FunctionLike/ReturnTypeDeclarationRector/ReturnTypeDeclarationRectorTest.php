<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;

final class ReturnTypeDeclarationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $files = [
            __DIR__ . '/Fixture/PhpCsFixerReturn/various.php.inc',

            __DIR__ . '/Fixture/skip_mixed_and_string.php.inc',
            __DIR__ . '/Fixture/skip_self.php.inc',

            // static types
            __DIR__ . '/Fixture/void_type.php.inc',
            __DIR__ . '/Fixture/no_void_abstract.php.inc',
            __DIR__ . '/Fixture/code_over_doc_priority.php.inc',
            __DIR__ . '/Fixture/known_static.php.inc',
            __DIR__ . '/Fixture/known_static_void.php.inc',
            __DIR__ . '/Fixture/known_static_method.php.inc',
            __DIR__ . '/Fixture/known_static_object.php.inc',
            __DIR__ . '/Fixture/known_static_object_parent.php.inc',
            __DIR__ . '/Fixture/known_static_conflicts.php.inc',

            __DIR__ . '/Fixture/known_static_nullable.php.inc',
            __DIR__ . '/Fixture/known_static_nullable_float.php.inc',
            __DIR__ . '/Fixture/known_float.php.inc',
            __DIR__ . '/Fixture/trait_interface.php.inc',
            __DIR__ . '/Fixture/this.php.inc',
            __DIR__ . '/Fixture/false.php.inc',
            __DIR__ . '/Fixture/complex_array.php.inc',
            __DIR__ . '/Fixture/generator.php.inc',
            __DIR__ . '/Fixture/PhpCsFixerReturn/invalid_class.php.inc',
            __DIR__ . '/Fixture/PhpCsFixerReturn/invalid_return.php.inc',
            __DIR__ . '/Fixture/PhpCsFixerReturn/blacklisted_class_methods.php.inc',
            __DIR__ . '/Fixture/PhpCsFixerReturn/various_2.php.inc',
            __DIR__ . '/Fixture/PhpCsFixerReturn/arrays.php.inc',
            __DIR__ . '/Fixture/PhpCsFixerReturn/skip.php.inc',
            __DIR__ . '/Fixture/PhpCsFixerReturn/skip_union_object_object_type.php.inc',
            __DIR__ . '/Fixture/PhpCsFixerReturn/nullables.php.inc',
            __DIR__ . '/Fixture/nikic/iterable.php.inc',
            __DIR__ . '/Fixture/nikic/null.php.inc',
            __DIR__ . '/Fixture/nikic/nullable.php.inc',
            __DIR__ . '/Fixture/nikic/object.php.inc',
            __DIR__ . '/Fixture/nikic/unsupported_types.php.inc',
            __DIR__ . '/Fixture/dunglas/BarInterface.php.inc',
            __DIR__ . '/Fixture/dunglas/BazTrait.php.inc',
            __DIR__ . '/Fixture/dunglas/Child.php.inc',
            __DIR__ . '/Fixture/dunglas/nullable_types.php.inc',
            // anonymous class
            __DIR__ . '/Fixture/a_new_class.php.inc',
        ];

        $this->doTestFiles($files);
    }

    protected function getRectorClass(): string
    {
        return ReturnTypeDeclarationRector::class;
    }
}
