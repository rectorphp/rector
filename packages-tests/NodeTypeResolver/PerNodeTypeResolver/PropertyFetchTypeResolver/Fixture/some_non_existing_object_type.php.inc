<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

final class SomeNonExistingObjectType
{
    public function run(ClassWithTypedPropertyTypes $props): void
    {
        $props->nonexistent->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\ObjectType;

return new ObjectType('Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\IDontExist');
