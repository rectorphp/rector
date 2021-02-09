<?php

use Rector\Php74\Rector\Property\TypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(TypedPropertyRector::class)->call('configure', [[
        TypedPropertyRector::CLASS_LIKE_TYPE_ONLY => true,
    ]]);
};
