<?php

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\FirstDependency;

final class SkipDifferentMethod
{
    public function __construct(
        private FirstDependency $firstDependency
    ) {
    }

    public function run()
    {
        $this->firstDependency->another();
    }
}
