<?php

namespace Rector\Php74\Tests\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector\Fixture;

class KeepReturnedValue
{
    public function go(\ReflectionFunction $reflectionFunction)
    {
        return $reflectionFunction->getReturnType();
    }
}
