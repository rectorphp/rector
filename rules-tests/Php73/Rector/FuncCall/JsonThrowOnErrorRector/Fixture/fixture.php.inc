<?php

namespace Rector\Php73\Tests\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function jsonThrowOnError()
{
    json_encode($content);
    json_decode($json);

    json_decode($json, true, 215);

    json_decode($json, true, 122, JSON_THROW_ON_ERROR);
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function jsonThrowOnError()
{
    json_encode($content, JSON_THROW_ON_ERROR);
    json_decode($json, false, 512, JSON_THROW_ON_ERROR);

    json_decode($json, true, 215, JSON_THROW_ON_ERROR);

    json_decode($json, true, 122, JSON_THROW_ON_ERROR);
}

?>
