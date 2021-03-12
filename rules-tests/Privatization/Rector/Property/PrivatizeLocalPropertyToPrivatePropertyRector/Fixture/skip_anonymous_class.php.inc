<?php

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

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
