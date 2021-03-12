<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function methodCallAsArgument()
{
    reset((new \stdClass())->bar());
    reset((new \stdClass())->bar()->baz());
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function methodCallAsArgument()
{
    $bar = (new \stdClass())->bar();
    reset($bar);
    $baz = (new \stdClass())->bar()->baz();
    reset($baz);
}

?>
