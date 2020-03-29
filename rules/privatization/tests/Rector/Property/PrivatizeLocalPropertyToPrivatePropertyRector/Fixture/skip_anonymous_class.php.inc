<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeLocalOnlyMethodRector\Fixture;

use PhpParser\NodeTraverser;

class SkipAnonymousClass
{
    public function run()
    {
        $anonymousClass = new class() extends NodeTraverser
        {
            public $property;
        };
    }
}
