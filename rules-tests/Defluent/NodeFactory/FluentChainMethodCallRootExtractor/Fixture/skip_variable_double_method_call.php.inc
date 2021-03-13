<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\NodeFactory\FluentChainMethodCallRootExtractor\Fixture;

use Rector\Tests\Defluent\NodeFactory\FluentChainMethodCallRootExtractor\Source\SomeClassWithFluentMethods;

final class SkipVariableDoubleMethodCall
{
    public function run()
    {
        $mainVariable = new SomeClassWithFluentMethods();
        $mainVariable->one()->two();
    }
}
