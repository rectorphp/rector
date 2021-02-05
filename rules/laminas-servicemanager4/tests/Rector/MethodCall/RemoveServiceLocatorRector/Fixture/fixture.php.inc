<?php

namespace Rector\LaminasServiceManager4\Tests\Rector\MethodCall\RemoveServiceLocatorRector\Fixture;

use Laminas\ServiceManager\ServiceLocatorInterface;

class Fixture
{
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator->getServiceLocator();
    }
}

?>
-----
<?php

namespace Rector\LaminasServiceManager4\Tests\Rector\MethodCall\RemoveServiceLocatorRector\Fixture;

use Laminas\ServiceManager\ServiceLocatorInterface;

class Fixture
{
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator;
    }
}

?>
