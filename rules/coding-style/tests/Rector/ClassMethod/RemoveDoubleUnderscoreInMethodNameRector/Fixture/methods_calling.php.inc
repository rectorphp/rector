<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector\Fixture;

final class MethodsCalling
{
    public function run(MyClass $myClass)
    {
        $myClass->__doSomething();

        $myClass->__construct();
        $myClass->__destruct();
        $myClass->__call();
        $myClass->__callStatic();
        $myClass->__get();
        $myClass->__set();
        $myClass->__isset();
        $myClass->__unset();
        $myClass->__sleep();
        $myClass->__wakeup();
        $myClass->__serialize();
        $myClass->__unserialize();
        $myClass->__toString();
        $myClass->__invoke();
        $myClass->__set_state();
        $myClass->__debugInfo();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector\Fixture;

final class MethodsCalling
{
    public function run(MyClass $myClass)
    {
        $myClass->doSomething();

        $myClass->__construct();
        $myClass->__destruct();
        $myClass->__call();
        $myClass->__callStatic();
        $myClass->__get();
        $myClass->__set();
        $myClass->__isset();
        $myClass->__unset();
        $myClass->__sleep();
        $myClass->__wakeup();
        $myClass->__serialize();
        $myClass->__unserialize();
        $myClass->__toString();
        $myClass->__invoke();
        $myClass->__set_state();
        $myClass->__debugInfo();
    }
}

?>
