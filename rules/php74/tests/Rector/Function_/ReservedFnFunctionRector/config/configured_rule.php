<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Php74\Rector\Function_\ReservedFnFunctionRector::class)->call('configure', [[
        \Rector\Php74\Rector\Function_\ReservedFnFunctionRector::RESERVED_NAMES_TO_NEW_ONES => [
            // for testing purposes of "fn" even on PHP 7.3-
            'reservedFn' => 'f',
        ],
    ]]);
};
