<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

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

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

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
