<?php

namespace Rector\Legacy\Tests\Rector\Include_\AddTopIncludeRector\Fixture;

class SomeClass
{

    private function someMethod() : void
    {
        include 'somefile.php';
    }

}
