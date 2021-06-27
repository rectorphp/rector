<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

use Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source\InvokableConstructorArgument;

function invokable()
{
    $invokable = new InvokableConstructorArgument();
    $invokable(bar());
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

use Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source\InvokableConstructorArgument;

function invokable()
{
    $invokable = new InvokableConstructorArgument();
    $bar = bar();
    $invokable($bar);
}

?>
