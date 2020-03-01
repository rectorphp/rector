<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

class SkipFuncGetArgs
{
    public static function merge(array $data, $merge): array
    {
        $args = array_slice(func_get_args(), 1);
        $return = $data;

        return [$return, $data, $args];
    }
}
