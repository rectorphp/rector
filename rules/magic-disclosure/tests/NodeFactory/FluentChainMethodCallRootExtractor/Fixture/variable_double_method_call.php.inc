<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Fixture;

use Rector\MagicDisclosure\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Source\SomeClassWithFluentMethods;

final class VariableDoubleMethodCall
{
    public function run()
    {
        $mainVariable = new SomeClassWithFluentMethods();
        $mainVariable->one()->two();
    }
}
