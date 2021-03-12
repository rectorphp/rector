<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use ReflectionProperty;

interface InterfaceReflection
{
    public function method(ReflectionProperty $propertyReflection);
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use ReflectionProperty;

interface InterfaceReflection
{
    public function method(ReflectionProperty $reflectionProperty);
}

?>
