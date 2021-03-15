<?php

use Rector\Transform\Rector\FuncCall\FuncCallToConstFetchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(FuncCallToConstFetchRector::class)
        ->call('configure', [[
            FuncCallToConstFetchRector::FUNCTIONS_TO_CONSTANTS => [
                'php_sapi_name' => 'PHP_SAPI',
                'pi' => 'M_PI',
            ],
        ]]);
};
