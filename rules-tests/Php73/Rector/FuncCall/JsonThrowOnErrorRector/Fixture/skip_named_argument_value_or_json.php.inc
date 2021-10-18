<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function skipNamedArgumentValueOrJson($str, array $data){
    json_decode(json: $str);
    json_encode(value: $data);
}
