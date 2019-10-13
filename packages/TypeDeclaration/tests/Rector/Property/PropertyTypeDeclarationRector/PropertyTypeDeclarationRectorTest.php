<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector;

final class PropertyTypeDeclarationRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/constructor_param.php.inc'];
        yield [__DIR__ . '/Fixture/constructor_param_with_aliased_param.php.inc'];
        yield [__DIR__ . '/Fixture/complex.php.inc'];
        yield [__DIR__ . '/Fixture/single_nullable_return.php.inc'];
        yield [__DIR__ . '/Fixture/getter_type.php.inc'];
        yield [__DIR__ . '/Fixture/getter_type_from_var_doc.php.inc'];
        yield [__DIR__ . '/Fixture/constructor_param_with_nullable.php.inc'];
        yield [__DIR__ . '/Fixture/constructor_array_param_with_nullable.php.inc'];
        yield [__DIR__ . '/Fixture/constructor_assign.php.inc'];
        yield [__DIR__ . '/Fixture/phpunit_setup.php.inc'];
        yield [__DIR__ . '/Fixture/default_value.php.inc'];
        yield [__DIR__ . '/Fixture/doctrine_column.php.inc'];
        yield [__DIR__ . '/Fixture/doctrine_relation_to_many.php.inc'];
        yield [__DIR__ . '/Fixture/doctrine_relation_to_one.php.inc'];
        yield [__DIR__ . '/Fixture/doctrine_relation_target_entity_same_namespace.php.inc'];
        yield [__DIR__ . '/Fixture/setter_type.php.inc'];
        yield [__DIR__ . '/Fixture/skip_multi_vars.php.inc'];
        yield [__DIR__ . '/Fixture/skip_anonymous_class.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return PropertyTypeDeclarationRector::class;
    }
}
