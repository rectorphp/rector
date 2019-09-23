<?php

namespace Rector\Php70\Tests\Rector\FuncCall\EregToPregMatchRector\Fixture;

function eregToPregMatch3()
{
    split('hi', 'hi, she said');

    split('hi', 'hi, she said', 0);

    spliti('hi', 'hi, she said', 1);
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\EregToPregMatchRector\Fixture;

function eregToPregMatch3()
{
    preg_split('#hi#m', 'hi, she said');

    preg_split('#hi#m', 'hi, she said', 1);

    preg_split('#hi#mi', 'hi, she said', 1);
}

?>
