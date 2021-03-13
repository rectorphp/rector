<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class Invokable
{
    public function __invoke(&$bar) {}
}

function invokable()
{
    $invokable = new Invokable();
    $invokable(bar());
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class Invokable
{
    public function __invoke(&$bar) {}
}

function invokable()
{
    $invokable = new Invokable();
    $bar = bar();
    $invokable($bar);
}

?>
