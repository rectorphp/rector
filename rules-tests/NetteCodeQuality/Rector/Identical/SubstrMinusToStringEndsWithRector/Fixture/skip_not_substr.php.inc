<?php

namespace Rector\Tests\NetteCodeQuality\Rector\Identical\SubstrMinusToStringEndsWithRector\Fixture;

function skipNotSustr($var)
{
    strlen($var) === 5;
}

?>
