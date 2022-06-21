<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

final class NullableObjectType
{
    public function run(ClassWithTypedPropertyTypes $props): void
    {
        $props->abcNullable->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\Abc;

return new UnionType([new ObjectType(Abc::class), new NullType()]);
