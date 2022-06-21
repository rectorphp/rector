<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

final class SomeObjectType
{
    public function run(ClassWithTypedPropertyTypes $props): void
    {
        $props->abc->xxx();
    }
}

?>
-----
<?php

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\Abc;
use PHPStan\Type\ObjectType;

return new ObjectType(Abc::class);
