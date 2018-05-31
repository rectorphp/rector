<?php declare(strict_types=1);

use Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\SomeContainer;

$container = new SomeContainer();
$container->addService('someService', new SomeService);

$parameters = $container->parameters;
