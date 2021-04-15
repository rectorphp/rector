<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function SkipJsonLastErrorNext2()
{
    json_decode($str);
    return json_last_error() === JSON_ERROR_NONE
}

?>
