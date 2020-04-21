<?php

namespace Rector\Php74\Tests\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector\Fixture;

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

namespace Rector\Php74\Tests\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector\Fixture;

class ParameterType
{
    public function go(\ReflectionType $reflectionType)
    {
        $name = $reflectionType->getName();
    }
}

?>
