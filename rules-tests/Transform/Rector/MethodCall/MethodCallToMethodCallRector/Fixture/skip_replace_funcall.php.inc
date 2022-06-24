<?php

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\FirstDependency;

function aFunctionReturningFirstDependency() : FirstDependency {
    return new FirstDependency();
}

aFunctionReturningFirstDependency()->go();
?>
