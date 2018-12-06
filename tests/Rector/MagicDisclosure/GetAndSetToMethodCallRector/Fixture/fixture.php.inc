<?php

use Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\SomeContainer;

function getAndSetToMethodCall()
{
    $container = new SomeContainer();
    $someService = $container->someService;

    $parameters = $container->parameters;
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
}

?>
