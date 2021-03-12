<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

use PhpParser\NodeTraverser;

class SkipAnonymousClass
{
    /**
     * @api
     */
    public function run()
    {
        $anonymousClass = new class() extends NodeTraverser
        {
            public function traverse(array $nodes): array
            {
            }
        };
    }
}
