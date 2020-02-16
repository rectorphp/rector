<?php

namespace Rector\Core\Tests\Issues\Issue2863\Fixture;

class Fixture2863KeepMultilineSingleAsterisk
{
    public function baz($baz)
    {
        /*
        should
        not
        be changed
        */

        return $baz;
    }
}
