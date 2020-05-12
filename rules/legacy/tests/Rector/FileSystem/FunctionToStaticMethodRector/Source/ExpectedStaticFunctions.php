<?php

namespace Rector\Legacy\Tests\Rector\FileSystem\FunctionToStaticMethodRector\Source;

final class StaticFunctions
{
    public static function firstStaticFunction()
    {
        return 5;
    }
}
$value = \Rector\Legacy\Tests\Rector\FileSystem\FunctionToStaticMethodRector\Source\StaticFunctions::firstStaticFunction();
