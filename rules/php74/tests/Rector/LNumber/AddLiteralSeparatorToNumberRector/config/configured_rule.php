<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector::class)->call('configure', [[
        \Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector::LIMIT_VALUE => 1000000,
    ]]);
};
