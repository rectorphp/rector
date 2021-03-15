<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\NodeFactory\FluentChainMethodCallRootExtractor\Fixture;

use Rector\Tests\Defluent\NodeFactory\FluentChainMethodCallRootExtractor\Source\AnotherTypeFactory;

final class SkipIsFactoryVariableDoubleMethodCall
{
    public function run()
    {
        $mainVariable = new AnotherTypeFactory();
        $someClassWithFluentMethods = $mainVariable->createSomeClassWithFluentMethods()->one();
    }
}
