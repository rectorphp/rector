<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class MyClass
{
    public static function staticMethod(&$bar) {}
}

function stringyCalls()
{
    $functionName = 'reset';
    $functionName(bar());

    $methodName = MyClass::class.'::staticMethod';
    $methodName(bar());
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class MyClass
{
    public static function staticMethod(&$bar) {}
}

function stringyCalls()
{
    $functionName = 'reset';
    $bar = bar();
    $functionName($bar);

    $methodName = MyClass::class.'::staticMethod';
    $bar = bar();
    $methodName($bar);
}

?>
