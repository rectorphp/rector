<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Fixture;

use Rector\Defluent\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Source\SomeClassWithFluentMethods;

final class VariableDoubleMethodCall
{
    public function run()
    {
        $mainVariable = new SomeClassWithFluentMethods();
        $mainVariable->one()->two();
    }
}
