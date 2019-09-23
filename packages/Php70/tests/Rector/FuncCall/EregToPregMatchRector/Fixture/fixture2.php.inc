<?php

namespace Rector\Php70\Tests\Rector\FuncCall\EregToPregMatchRector\Fixture;

function eregToPregMatch2()
{
    ereg_replace('hi', '\\1', 'hi, she said');

    eregi_replace('hi', '\\1', 'hi, she said');
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\EregToPregMatchRector\Fixture;

function eregToPregMatch2()
{
    preg_replace('#hi#m', '\\1', 'hi, she said');

    preg_replace('#hi#mi', '\\1', 'hi, she said');
}

?>
