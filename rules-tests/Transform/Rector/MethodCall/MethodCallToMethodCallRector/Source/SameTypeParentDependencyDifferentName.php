<?php

declare(strict_types=1);

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source;

abstract class SameTypeParentDependencyDifferentName
{
    /**
     * @inject
     */
    public SecondDependency $wooohoooo;
}
