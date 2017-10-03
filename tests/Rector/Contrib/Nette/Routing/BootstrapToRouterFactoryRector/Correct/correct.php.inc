<?php declare (strict_types=1);

use Nette\Application\Routers\Route;

$configurator = new Nette\Config\Configurator;

$container = $configurator->createContainer();

return $container;
