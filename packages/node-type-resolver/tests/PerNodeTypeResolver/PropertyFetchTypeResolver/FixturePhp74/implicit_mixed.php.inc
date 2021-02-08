<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\FixturePhp74;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

final class ImplicitMixed
{
    public function run(ClassWithTypedPropertyTypes $props): void
    {
        $props->implicitMixed->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\MixedType;

return new MixedType();
