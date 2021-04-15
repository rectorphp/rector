<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function SkipJsonLastErrorNext3($str)
{
    if (rand(0, 1)) {
        json_decode($str);
    }

    json_last_error();
    json_last_error_msg();
}

?>
