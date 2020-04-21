<?php

namespace Rector\Php74\Tests\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector\Fixture;

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
