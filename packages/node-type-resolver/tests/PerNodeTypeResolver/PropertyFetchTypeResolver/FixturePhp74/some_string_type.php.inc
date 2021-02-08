<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\FixturePhp74;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

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
