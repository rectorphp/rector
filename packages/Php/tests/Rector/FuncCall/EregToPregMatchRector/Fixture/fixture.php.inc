<?php

namespace Rector\Php\Tests\Rector\FuncCall\EregToPregMatchRector\Wrong;

function eregToPregMatch()
{
    ereg('hi', 'hi, she said');

    eregi('hi', 'hi, she said');

    $pattern = 'hi';
    ereg($pattern, 'hi, she said');
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\EregToPregMatchRector\Wrong;

function eregToPregMatch()
{
    preg_match('#hi#m', 'hi, she said');

    preg_match('#hi#mi', 'hi, she said');

    $pattern = 'hi';
    preg_match('#' . preg_quote($pattern, '#') . '#m', 'hi, she said');
}

?>
