<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

final class SomeFullyQualifiedObjectType
{
    public function run(ClassWithTypedPropertyTypes $props): void
    {
        $props->abcFQ->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\ObjectType;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\Abc;

return new ObjectType(Abc::class);
