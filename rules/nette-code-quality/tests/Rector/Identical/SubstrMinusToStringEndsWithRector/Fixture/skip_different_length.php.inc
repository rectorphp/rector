<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Identical\SubstrMinusToStringEndsWithRector\Fixture;

function skipDifferentLength($var)
{
    'part' !== substr($var, -3);
}

?>
