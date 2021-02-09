<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\FuncCall\FuncCallToNewRector::class)->call('configure', [[
        \Rector\Transform\Rector\FuncCall\FuncCallToNewRector::FUNCTIONS_TO_NEWS => [
            'collection' => ['Collection'],
        ],
    ]]);
};
