<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

use Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source\VariousCallsClass;

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

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

use Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source\VariousCallsClass;

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
