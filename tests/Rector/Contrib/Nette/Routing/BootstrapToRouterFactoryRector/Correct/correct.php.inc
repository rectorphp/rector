<?php declare(strict_types=1);

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Config\Configurator;

$configurator = new Configurator;

$container = $configurator->createContainer();




return $container;
