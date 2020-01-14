<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class Bar {}

function new_()
{
    reset(new \stdClass());
    reset(new Bar());
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class Bar {}

function new_()
{
    $stdClass = new \stdClass();
    reset($stdClass);
    $bar = new Bar();
    reset($bar);
}

?>
