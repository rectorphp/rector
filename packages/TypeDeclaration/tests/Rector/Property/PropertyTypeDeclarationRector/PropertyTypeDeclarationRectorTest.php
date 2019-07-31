<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector;

final class PropertyTypeDeclarationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/constructor_param.php.inc',
            __DIR__ . '/Fixture/constructor_param_with_nullable.php.inc',
            __DIR__ . '/Fixture/constructor_assign.php.inc',
            __DIR__ . '/Fixture/phpunit_setup.php.inc',
            __DIR__ . '/Fixture/default_value.php.inc',

            __DIR__ . '/Fixture/doctrine_column.php.inc',
            __DIR__ . '/Fixture/doctrine_relation.php.inc',

            __DIR__ . '/Fixture/complex.php.inc',

            __DIR__ . '/Fixture/single_nullable_return.php.inc',
            // skip
            __DIR__ . '/Fixture/skip_multi_vars.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return PropertyTypeDeclarationRector::class;
    }
}
