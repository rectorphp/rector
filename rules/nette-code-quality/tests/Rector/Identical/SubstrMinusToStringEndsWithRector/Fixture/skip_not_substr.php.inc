<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Identical\SubstrMinusToStringEndsWithRector\Fixture;

function skipNotSustr($var)
{
    strlen($var) === 5;
}

?>
