<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Source;

final class AnotherTypeFactory
{
    /**
     * @return SomeClassWithFluentMethods
     */
    public function createSomeClassWithFluentMethods()
    {
        return new SomeClassWithFluentMethods();
    }
}
