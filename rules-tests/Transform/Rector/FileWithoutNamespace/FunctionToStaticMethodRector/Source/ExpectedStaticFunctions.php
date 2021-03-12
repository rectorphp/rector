<?php

namespace Rector\Tests\Transform\Rector\FileWithoutNamespace\FunctionToStaticMethodRector\Fixture;

final class StaticFunctions
{
    public static function firstStaticFunction()
    {
        return 5;
    }
}
