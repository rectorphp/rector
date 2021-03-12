<?php

use Rector\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(PrivatizeLocalOnlyMethodRector::class);
};
