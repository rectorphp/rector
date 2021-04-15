<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function JsonLastErrorNextAfterReturn($str)
{
    if (rand(0, 1)) {
        json_decode($str);
        return;
    }

    json_last_error();
    json_last_error_msg();
}

?>
-----
<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function JsonLastErrorNextAfterReturn($str)
{
    if (rand(0, 1)) {
        json_decode($str, null, 512, JSON_THROW_ON_ERROR);
        return;
    }

    json_last_error();
    json_last_error_msg();
}

?>