<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\FixturePhp74;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

final class NonExistingObjectType
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

return new ObjectType('Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\IDontExist');
