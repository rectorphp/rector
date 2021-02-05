<?php

namespace Rector\Laminas\Tests\Rector\MethodCall\RemoveServiceLocatorRector\Fixture;

class SkipNotServicelocator
{
    public function __invoke(NotServiceLocator $foo)
    {
        $foo->getServiceLocator();
    }
}