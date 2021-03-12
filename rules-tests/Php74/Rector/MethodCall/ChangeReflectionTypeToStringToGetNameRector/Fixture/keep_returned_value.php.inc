<?php

namespace Rector\Tests\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector\Fixture;

class KeepReturnedValue
{
    public function go(\ReflectionFunction $reflectionFunction)
    {
        return $reflectionFunction->getReturnType();
    }
}
