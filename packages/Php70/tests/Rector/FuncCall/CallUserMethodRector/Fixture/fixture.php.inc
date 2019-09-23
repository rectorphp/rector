<?php

namespace Rector\Php70\Tests\Rector\FuncCall\CallUserMethodRector\Fixture;

function callUserMethod()
{
    $obj = new stdClass();
    $method_name = 'hi';

    call_user_method_array($method_name, $obj, $params);

    call_user_method($method_name, $obj, $params);
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\CallUserMethodRector\Fixture;

function callUserMethod()
{
    $obj = new stdClass();
    $method_name = 'hi';

    call_user_func_array([$obj, $method_name], $params);

    call_user_func([$obj, $method_name], $params);
}

?>
