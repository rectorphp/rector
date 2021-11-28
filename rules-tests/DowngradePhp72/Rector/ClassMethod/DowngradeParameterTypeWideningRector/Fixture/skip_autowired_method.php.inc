<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

use Rector\NodeNameResolver\NodeNameResolver;
use Symfony\Contracts\Service\Attribute\Required;

class SkipAutowiredMethod
{
    #[Required]
    public function autowire(NodeNameResolver $nodeNameResolver)
    {
    }
}
