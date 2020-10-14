<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Fixture;

use Rector\Defluent\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Source\SomeClassWithFluentMethods;

final class ReturnNewDoubleMethodCall
{
    public function run()
    {
        return (new SomeClassWithFluentMethods())->one()->two();
    }
}
