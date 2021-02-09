<?php

use Rector\Transform\Rector\FuncCall\FuncCallToNewRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(FuncCallToNewRector::class)->call('configure', [[
        FuncCallToNewRector::FUNCTIONS_TO_NEWS => [
            'collection' => ['Collection'],
        ],
    ]]);
};
