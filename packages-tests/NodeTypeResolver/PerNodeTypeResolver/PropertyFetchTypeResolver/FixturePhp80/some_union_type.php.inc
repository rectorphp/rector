<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\FixturePhp80;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithNativePropsPhp80;

final class SomeUnionType
{
    public function run(ClassWithNativePropsPhp80 $props): void
    {
        $props->abcOrString->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\Abc;

return new UnionType([new ObjectType(Abc::class), new StringType()]);
