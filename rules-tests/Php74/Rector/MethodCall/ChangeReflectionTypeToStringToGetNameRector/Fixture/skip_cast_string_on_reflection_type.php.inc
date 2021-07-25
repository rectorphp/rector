<?php

namespace Rector\Tests\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector\Fixture;

use ReflectionMethod;
use ReflectionNamedType;

class SkipCastStringOnReflectionType
{
    public function go(ReflectionMethod $reflectionMethod)
    {
        $returnType = $reflectionMethod->getReturnType();

        return $returnType instanceof ReflectionNamedType
            ? $returnType->getName()
            : (string) $returnType;
    }
}

?>
