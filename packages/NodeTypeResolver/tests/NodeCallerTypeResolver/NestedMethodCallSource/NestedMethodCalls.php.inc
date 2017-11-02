<?php

use Nette\Config\Configurator;

$configurator = new Configurator;

$configurator->createContainer()
    ->addService('name', 'service')
    ->getParameters();
