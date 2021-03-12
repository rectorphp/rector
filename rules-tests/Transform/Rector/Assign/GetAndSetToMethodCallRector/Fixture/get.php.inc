<?php

use Rector\Transform\Tests\Rector\Assign\GetAndSetToMethodCallRector\Source\SomeContainer;

function getAndSetToMethodCall()
{
    $container = new SomeContainer();
    $someService = $container->someService;

    $parameters = $container->parameters;

    if ($container->someService) {

    }
}

?>
-----
<?php

use Rector\Transform\Tests\Rector\Assign\GetAndSetToMethodCallRector\Source\SomeContainer;

function getAndSetToMethodCall()
{
    $container = new SomeContainer();
    $someService = $container->getService('someService');

    $parameters = $container->parameters;

    if ($container->getService('someService')) {

    }
}

?>
