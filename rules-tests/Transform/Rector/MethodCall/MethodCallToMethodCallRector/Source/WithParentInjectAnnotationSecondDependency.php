<?php

declare(strict_types=1);

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source;

abstract class WithParentInjectAnnotationSecondDependency
{
    /**
     * @inject
     */
    public SecondDependency $secondDependency;
}
