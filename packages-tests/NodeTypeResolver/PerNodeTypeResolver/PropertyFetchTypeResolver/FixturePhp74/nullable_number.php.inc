<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\FixturePhp74;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

final class NullableNumber
{
    public function run(ClassWithTypedPropertyTypes $props): void
    {
        $props->numberNullable->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\UnionType;

return new UnionType([new IntegerType(), new NullType()]);
