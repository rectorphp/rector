<?php

namespace Rector\Tests\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector\Fixture;

class ParameterType
{
    public function go(\ReflectionType $reflectionType)
    {
        $name = (string) $reflectionType;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector\Fixture;

class ParameterType
{
    public function go(\ReflectionType $reflectionType)
    {
        $name = $reflectionType->getName();
    }
}

?>
