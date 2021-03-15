<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

use Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source\VariousCallsClass;

final class MethodCalls
{
    public function run()
    {
        VariousCallsClass::staticMethod(bar());
        $aClass = new VariousCallsClass();
        $aClass->baz(baz());
        $aClass->child()->bar(bar());
    }
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

use Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source\VariousCallsClass;

final class MethodCalls
{
    public function run()
    {
        $bar = bar();
        VariousCallsClass::staticMethod($bar);
        $aClass = new VariousCallsClass();
        $baz = baz();
        $aClass->baz($baz);
        $bar2 = bar();
        $aClass->child()->bar($bar2);
    }
}

?>
