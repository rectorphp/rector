<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\FixturePhp74;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

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

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\Abc;
use PHPStan\Type\ObjectType;

return new ObjectType(Abc::class);
