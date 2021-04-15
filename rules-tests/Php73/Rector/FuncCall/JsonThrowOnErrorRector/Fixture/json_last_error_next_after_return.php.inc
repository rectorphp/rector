<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function SkipJsonLastErrorNextAfterReturn()
{
    json_decode($str);
    return;

    json_last_error();
    json_last_error_msg();
}

?>
-----
<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function SkipJsonLastErrorNextAfterReturn()
{
    json_decode($str, null, 512, JSON_THROW_ON_ERROR);
    return;

    json_last_error();
    json_last_error_msg();
}

?>