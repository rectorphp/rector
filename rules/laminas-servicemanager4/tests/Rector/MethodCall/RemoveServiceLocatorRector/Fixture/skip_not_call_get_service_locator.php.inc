<?php

namespace Rector\LaminasServiceManager4\Tests\Rector\MethodCall\RemoveServiceLocatorRector\Fixture;

use Laminas\ServiceManager\ServiceLocatorInterface;

class SkipNotCallGetServiceLocator
{
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator->get('foo');
    }
}

?>
