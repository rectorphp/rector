<?php declare(strict_types=1);

$container = new Nette\DI\Container;
$someService = $container->someService;
$container->someService = new SomeService;

$parameters = $container->parameters;
