<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class MyClass
{
    public static function staticMethod(&$bar) {}
}

function stringyCalls()
{
    'reset'(bar());

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
    $bar = bar();
    'reset'($bar);

    $functionName = 'reset';
    $bar2 = bar();
    $functionName($bar2);

    $methodName = MyClass::class.'::staticMethod';
    $bar3 = bar();
    $methodName($bar3);
}

?>
