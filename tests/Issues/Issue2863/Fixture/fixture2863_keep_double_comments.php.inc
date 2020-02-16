<?php

namespace Rector\Core\Tests\Issues\Issue2863\Fixture;

class Fixture2863KeepDoubleComments
{
    /*****************************************
     * should not be removed
     ****************************************/
    /**
     * Some multi line
     * dock block
     */
    public function bizz()
    {
        return null;
    }
}
