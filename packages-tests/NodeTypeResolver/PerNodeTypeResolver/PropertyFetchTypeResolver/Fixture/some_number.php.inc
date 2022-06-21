<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

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
