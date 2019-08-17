<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;

final class AddArrayReturnDocTypeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/setter_based.php.inc',
            __DIR__ . '/Fixture/fully_qualified_name.php.inc',
            __DIR__ . '/Fixture/yield_strings.php.inc',
            __DIR__ . '/Fixture/simple_array.php.inc',
            __DIR__ . '/Fixture/add_without_return_type_declaration.php.inc',
            __DIR__ . '/Fixture/fix_incorrect_array.php.inc',
            // skip
            __DIR__ . '/Fixture/skip_constructor.php.inc',
            __DIR__ . '/Fixture/skip_array_after_array_type.php.inc',
            __DIR__ . '/Fixture/skip_shorten_class_name.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return AddArrayReturnDocTypeRector::class;
    }
}
