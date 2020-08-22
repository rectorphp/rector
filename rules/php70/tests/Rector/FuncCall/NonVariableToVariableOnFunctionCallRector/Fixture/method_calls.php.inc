<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class Child
{
    public function bar(&$bar) {}
}

class AClass
{
    public static function staticMethod(&$bar) {}

    public function baz(&$bar) {}

    public function child(): Child
    {
        return new Child();
    }
}

function methodCalls()
{
    AClass::staticMethod(bar());
    $aClass = new AClass();
    $aClass->baz(baz());
    $aClass->child()->bar(bar());
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class Child
{
    public function bar(&$bar) {}
}

class AClass
{
    public static function staticMethod(&$bar) {}

    public function baz(&$bar) {}

    public function child(): Child
    {
        return new Child();
    }
}

function methodCalls()
{
    $bar = bar();
    AClass::staticMethod($bar);
    $aClass = new AClass();
    $baz = baz();
    $aClass->baz($baz);
    $bar2 = bar();
    $aClass->child()->bar($bar2);
}

?>
