<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;

$container = new ContainerBuilder();
$container->register('foo', 'stdClass')
    ->setScope(ContainerBuilder::SCOPE_PROTOTYPE);
