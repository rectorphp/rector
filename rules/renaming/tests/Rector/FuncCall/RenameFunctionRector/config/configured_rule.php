<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\FuncCall\RenameFunctionRector::class)->call('configure', [[
        \Rector\Renaming\Rector\FuncCall\RenameFunctionRector::OLD_FUNCTION_TO_NEW_FUNCTION => [
            'view' => 'Laravel\Templating\render',
            'sprintf' => 'Safe\sprintf',
        ],
    ]]);
};
