<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Fixture;

use Rector\MagicDisclosure\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Source\AnotherTypeFactory;

final class IsFactoryVariableDoubleMethodCall
{
    public function run()
    {
        $mainVariable = new AnotherTypeFactory();
        $someClassWithFluentMethods = $mainVariable->createSomeClassWithFluentMethods()->one();
    }
}
