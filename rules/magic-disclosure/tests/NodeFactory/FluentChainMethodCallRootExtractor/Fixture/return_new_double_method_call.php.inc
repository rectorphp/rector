<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Fixture;

use Rector\MagicDisclosure\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Source\SomeClassWithFluentMethods;

final class ReturnNewDoubleMethodCall
{
    public function run()
    {
        return (new SomeClassWithFluentMethods())->one()->two();
    }
}
