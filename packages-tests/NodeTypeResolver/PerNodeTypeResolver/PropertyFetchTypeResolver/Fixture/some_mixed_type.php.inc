<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithNativePropsPhp80;

final class SomeMixedType
{
    public function run(ClassWithNativePropsPhp80 $props): void
    {
        $props->explicitMixed->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\MixedType;

return new MixedType();
