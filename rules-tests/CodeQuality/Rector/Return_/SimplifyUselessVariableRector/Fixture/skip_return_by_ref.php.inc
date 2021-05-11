<?php

namespace Rector\Tests\CodeQuality\Rector\Return_\SimplifyUselessVariableRector\Fixture;

class Foobar {
    function & test()
    {
        $var = 9000;
        return $var;
    }
}


function & () {
    $var = 26;
    return $var;
};

function & notAnonymous()
{
    for ($i = 0; $i < 4; $i++) {
        $var = 42;
        return $var;
    }
}
