<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Fixture;

use Rector\Defluent\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Source\SomeClassWithFluentMethods;

final class SkipVariableDoubleMethodCall
{
    public function run()
    {
        $mainVariable = new SomeClassWithFluentMethods();
        $mainVariable->one()->two();
    }
}
