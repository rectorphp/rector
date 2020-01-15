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
    $bar = bar();
    $functionName($bar);

    $methodName = MyClass::class.'::staticMethod';
    $bar = bar();
    $methodName($bar);
}

?>
