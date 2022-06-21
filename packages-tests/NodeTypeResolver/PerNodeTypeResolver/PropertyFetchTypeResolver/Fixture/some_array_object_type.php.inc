<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

final class SomeArrayObjectType
{
    public function run(ClassWithTypedPropertyTypes $props): void
    {
        $props->arrayOfAbcs->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\Abc;

return new ArrayType(new MixedType(), new ObjectType(Abc::class));
