<?php

namespace Rector\Core\Tests\Issues\Issue2863\Fixture;

class SomeClass
{
    public function foo($foo)
    {
        //thiscommentshouldnotberemoved
        return $foo;
    }

    public function bar($bar)
    {
        // @var string $bar['should_not_be_changed]

        return $bar['should_not_be_changed'];
    }

    public function baz($baz)
    {
        /*
        should
        not
        be changed
        */

        return $baz;
    }

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