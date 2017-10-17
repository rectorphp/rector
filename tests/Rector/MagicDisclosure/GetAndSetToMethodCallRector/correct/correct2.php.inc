<?php declare(strict_types=1);

$container = new Nette\DI\Container;
$container->addService('someService', new SomeService);

$parameters = $container->parameters;
