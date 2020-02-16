<?php

namespace Rector\Core\Tests\Issues\Issue2863\Fixture;

class Fixture2863KeepComment
{
    public function foo($foo)
    {
        //thiscommentshouldnotberemoved
        return $foo;
    }
}
