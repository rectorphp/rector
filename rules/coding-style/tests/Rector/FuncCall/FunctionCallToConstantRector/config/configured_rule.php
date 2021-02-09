<?php

use Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(FunctionCallToConstantRector::class)->call('configure', [[
        FunctionCallToConstantRector::FUNCTIONS_TO_CONSTANTS => [
            'php_sapi_name' => 'PHP_SAPI',
            'pi' => 'M_PI',
        ],
    ]]);
};
