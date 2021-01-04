<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Fixture;

use Rector\Defluent\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Source\AnotherTypeFactory;

final class SkipIsFactoryVariableDoubleMethodCall
{
    public function run()
    {
        $mainVariable = new AnotherTypeFactory();
        $someClassWithFluentMethods = $mainVariable->createSomeClassWithFluentMethods()->one();
    }
}
