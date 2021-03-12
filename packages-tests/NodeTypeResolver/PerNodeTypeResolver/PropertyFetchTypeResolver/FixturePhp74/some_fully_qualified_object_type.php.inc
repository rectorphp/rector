<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\FixturePhp74;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

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
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\Abc;

return new ObjectType(Abc::class);
