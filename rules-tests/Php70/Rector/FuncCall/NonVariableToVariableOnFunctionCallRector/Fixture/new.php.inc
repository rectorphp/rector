<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class Bar {}

function new_()
{
    reset(new \stdClass());
    reset(new Bar());
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class Bar {}

function new_()
{
    $stdClass = new \stdClass();
    reset($stdClass);
    $bar = new Bar();
    reset($bar);
}

?>
