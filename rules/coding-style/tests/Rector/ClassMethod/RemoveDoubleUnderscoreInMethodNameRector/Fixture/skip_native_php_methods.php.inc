<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector\Fixture;

class SkipNativePhpMethods
{
    function __construct(){}
    function __destruct(){}
    function __call($name, $value){}
    static function __callStatic($name, $value){}
    function __get($value){}
    function __set($key, $value){}
    function __isset($key){}
    function __unset($key){}
    function __sleep(){}
    function __wakeup(){}
    function __serialize(){}
    function __unserialize($content){}

    function __toString()
    {
        return 'something to keep contract';
    }

    function __invoke(){}
    static function __set_state($state){}
    function __clone(){}
    function __debugInfo(){}
}
