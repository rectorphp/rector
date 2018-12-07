<?php

namespace Rector\Php\Tests\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function jsonThrowOnError()
{
    json_encode($content);
    json_decode($json);
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function jsonThrowOnError()
{
    json_encode($content, JSON_THROW_ON_ERROR);
    json_decode($json, null, null, JSON_THROW_ON_ERROR);
}

?>
