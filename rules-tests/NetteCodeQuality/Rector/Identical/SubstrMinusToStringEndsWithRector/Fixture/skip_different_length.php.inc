<?php

namespace Rector\Tests\NetteCodeQuality\Rector\Identical\SubstrMinusToStringEndsWithRector\Fixture;

function skipDifferentLength($var)
{
    'part' !== substr($var, -3);
}

?>
