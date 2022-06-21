<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

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
