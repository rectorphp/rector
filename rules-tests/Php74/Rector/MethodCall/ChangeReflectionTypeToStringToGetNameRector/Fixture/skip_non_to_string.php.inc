<?php

namespace Rector\Tests\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector\Fixture;

use ReflectionParameter;
use ReflectionType;

class SkipNonToString
{
    public function go(ReflectionParameter $parameterReflection)
    {
        $parameterType = $parameterReflection->getType();
        if ($parameterType !== null) {
            /** @var ReflectionType $parameterType */
            $parameterType = $parameterType->getName();
        }
    }
}
