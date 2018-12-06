<?php

namespace Rector\Php\Tests\Rector\FuncCall\EregToPregMatchRector\Wrong;

function eregToPregMatch4()
{
    $result = ereg('hitom', '...hitom...', $matches);
    var_dump($result); // 5

    // no return value
    ereg('hitom', '...hitom...', $matches);
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\EregToPregMatchRector\Wrong;

function eregToPregMatch4()
{
    $result = preg_match('#hitom#m', '...hitom...', $matches) ? strlen($matches[0]) : false;
    var_dump($result); // 5

    // no return value
    preg_match('#hitom#m', '...hitom...', $matches);
}

?>
