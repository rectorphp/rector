<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function processContentFromParam(array $content = [])
{
    json_encode($content);
}

function processContentFromParam2(string $json)
{
    json_decode($json);
}

?>
-----
<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function processContentFromParam(array $content = [])
{
    json_encode($content, JSON_THROW_ON_ERROR);
}

function processContentFromParam2(string $json)
{
    json_decode($json, null, 512, JSON_THROW_ON_ERROR);
}

?>
