<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

class KeepMagicMethodsParam
{
    public function __call($one, $two)
    {
    }

    public function __get($one)
    {
    }

    public function __set($one, $value)
    {
    }

    public function __isset($one)
    {
    }

    public static function __callStatic($one, $two)
    {
    }
}
