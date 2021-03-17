<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\FixturePhp74;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

final class SomeStringType
{
    public function run(ClassWithTypedPropertyTypes $props): void
    {
        $props->text->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\StringType;

return new StringType();
