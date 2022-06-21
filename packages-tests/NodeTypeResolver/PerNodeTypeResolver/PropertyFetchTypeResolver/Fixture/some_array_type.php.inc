<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

final class SomeArrayType
{
    public function run(ClassWithTypedPropertyTypes $props): void
    {
        $props->array->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;

return new ArrayType(new MixedType(), new MixedType());
