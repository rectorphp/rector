<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function skipNamedArgumentFlags($str){
    json_decode($str, flags: JSON_THROW_ON_ERROR);
}

