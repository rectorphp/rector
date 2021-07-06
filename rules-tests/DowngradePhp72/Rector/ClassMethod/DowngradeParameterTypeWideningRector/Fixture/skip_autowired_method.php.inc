<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

use Rector\NodeNameResolver\NodeNameResolver;

class SkipAutowiredMethod
{
    /**
     * @required
     */
    public function autowireSomeMethod(NodeNameResolver $nodeNameResolver)
    {
    }
}
