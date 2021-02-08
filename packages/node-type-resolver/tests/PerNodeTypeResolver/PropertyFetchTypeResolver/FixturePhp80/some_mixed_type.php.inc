<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\FixturePhp80;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithNativePropsPhp80;

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
