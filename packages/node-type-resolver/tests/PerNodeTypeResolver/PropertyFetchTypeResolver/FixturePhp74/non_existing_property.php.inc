<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\FixturePhp74;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

final class NonExistingProperty
{
    public function run(ClassWithTypedPropertyTypes $props): void
    {
        $props->thisDoesNotExistOnTheObject->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\ErrorType;

return new ErrorType();
