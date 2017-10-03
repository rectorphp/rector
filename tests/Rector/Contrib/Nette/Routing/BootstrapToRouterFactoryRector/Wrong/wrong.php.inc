<?php declare (strict_types=1);

use Nette\Application\Routers\Route;

$configurator = new Nette\Config\Configurator;

$container = $configurator->createContainer();

$container->router = new Nette\Application\Routers\RouterList;
$container->router[] = new Router('index', 'Page:default');

return $container;
