<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithNativePropsPhp80;

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
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\Abc;

return new UnionType([new ObjectType(Abc::class), new StringType()]);
