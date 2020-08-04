<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use ReflectionProperty;

class ReflectionPropertyChange
{
    public function method(ReflectionProperty $propertyReflection)
    {
        if ($propertyReflection) {
        }

        if ($propertyReflection->getDocComment()) {
        }
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use ReflectionProperty;

class ReflectionPropertyChange
{
    public function method(ReflectionProperty $reflectionProperty)
    {
        if ($reflectionProperty) {
        }

        if ($reflectionProperty->getDocComment()) {
        }
    }
}

?>
