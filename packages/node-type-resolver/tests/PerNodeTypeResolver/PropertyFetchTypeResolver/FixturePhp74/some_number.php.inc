<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\FixturePhp74;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

final class SomeNumber
{
    public function run(ClassWithTypedPropertyTypes $props): void
    {
        $props->number->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\IntegerType;

return new IntegerType();
