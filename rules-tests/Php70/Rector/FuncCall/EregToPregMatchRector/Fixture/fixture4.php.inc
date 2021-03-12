<?php

namespace Rector\Php70\Tests\Rector\FuncCall\EregToPregMatchRector\Fixture;

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

namespace Rector\Php70\Tests\Rector\FuncCall\EregToPregMatchRector\Fixture;

function eregToPregMatch4()
{
    $result = preg_match('#hitom#m', '...hitom...', $matches) ? strlen($matches[0]) : false;
    var_dump($result); // 5

    // no return value
    preg_match('#hitom#m', '...hitom...', $matches);
}

?>
