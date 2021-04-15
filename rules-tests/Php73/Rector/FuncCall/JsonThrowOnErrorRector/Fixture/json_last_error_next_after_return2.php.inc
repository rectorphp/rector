<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function JsonLastErrorNextAfterReturn2($str)
{
    if (rand(0, 1)) {
        return json_decode($str);
    }

    json_last_error();
    json_last_error_msg();
}

?>
-----
<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function JsonLastErrorNextAfterReturn2($str)
{
    if (rand(0, 1)) {
        return json_decode($str, null, 512, JSON_THROW_ON_ERROR);
    }

    json_last_error();
    json_last_error_msg();
}

?>