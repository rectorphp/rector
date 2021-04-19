<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector\Fixture;

class SkipFuncGetArgs
{
    private static function merge(array $data, $merge): array
    {
        $args = array_slice(func_get_args(), 1);
        $return = $data;

        return [$return, $data, $args];
    }
}
