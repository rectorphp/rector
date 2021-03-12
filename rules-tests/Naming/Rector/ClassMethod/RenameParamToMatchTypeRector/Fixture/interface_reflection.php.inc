<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use ReflectionProperty;

interface InterfaceReflection
{
    public function method(ReflectionProperty $propertyReflection);
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use ReflectionProperty;

interface InterfaceReflection
{
    public function method(ReflectionProperty $reflectionProperty);
}

?>
