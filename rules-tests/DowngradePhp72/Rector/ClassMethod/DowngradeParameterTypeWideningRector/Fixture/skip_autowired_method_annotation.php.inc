<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

use Rector\NodeNameResolver\NodeNameResolver;

class SkipAutowiredMethodAnnotation
{
    /**
     * @required
     */
    public function autowire(NodeNameResolver $nodeNameResolver)
    {
    }
}
