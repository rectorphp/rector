<?php

use Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\SomeContainer;

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

use Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\SomeContainer;

function getAndSetToMethodCall()
{
    $container = new SomeContainer();
    $someService = $container->getService('someService');

    $parameters = $container->parameters;

    if ($container->getService('someService')) {

    }
}

?>
