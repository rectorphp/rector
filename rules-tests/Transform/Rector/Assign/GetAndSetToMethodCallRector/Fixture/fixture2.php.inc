<?php

use Rector\Tests\Transform\Rector\Assign\GetAndSetToMethodCallRector\Source\SomeContainer;

function getAndSetToMethodCall2()
{
    $container = new SomeContainer();
    $container->someService = new SomeService;

    $parameters = $container->parameters;
}

?>
-----
<?php

use Rector\Tests\Transform\Rector\Assign\GetAndSetToMethodCallRector\Source\SomeContainer;

function getAndSetToMethodCall2()
{
    $container = new SomeContainer();
    $container->addService('someService', new SomeService);

    $parameters = $container->parameters;
}

?>
