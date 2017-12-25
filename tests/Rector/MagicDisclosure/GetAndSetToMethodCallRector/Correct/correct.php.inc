<?php declare(strict_types=1);

$container = new Nette\DI\Container;
$someService = $container->getService('someService');

$parameters = $container->parameters;
