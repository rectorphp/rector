<?php

use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddLiteralSeparatorToNumberRector::class)->call('configure', [[
        AddLiteralSeparatorToNumberRector::LIMIT_VALUE => 1000000,
    ]]);
};
