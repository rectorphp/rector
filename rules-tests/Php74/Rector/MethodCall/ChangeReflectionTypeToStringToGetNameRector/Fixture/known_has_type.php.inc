<?php

namespace Rector\Tests\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector\Fixture;

class KnownHasType
{
    public function go(\ReflectionMethod $reflectionMethod)
    {
        if ($reflectionMethod->hasReturnType()) {
            return (string) $reflectionMethod->getReturnType();
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector\Fixture;

class KnownHasType
{
    public function go(\ReflectionMethod $reflectionMethod)
    {
        if ($reflectionMethod->hasReturnType()) {
            return $reflectionMethod->getReturnType()->getName();
        }
    }
}

?>
