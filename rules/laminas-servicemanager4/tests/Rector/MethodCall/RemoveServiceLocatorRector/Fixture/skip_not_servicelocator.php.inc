<?php

namespace Rector\LaminasServiceManager4\Tests\Rector\MethodCall\RemoveServiceLocatorRector\Fixture;

class SkipNotServiceLocator
{
    public function __invoke(NotServiceLocator $foo)
    {
        $foo->getServiceLocator();
    }
}