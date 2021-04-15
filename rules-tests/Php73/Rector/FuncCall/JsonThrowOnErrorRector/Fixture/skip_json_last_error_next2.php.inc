<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function SkipJsonLastErrorNext2()
{
    json_decode($str);
    json_last_error();
    json_last_error_msg();
}

?>
