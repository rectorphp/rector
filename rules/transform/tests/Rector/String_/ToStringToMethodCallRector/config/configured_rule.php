<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\String_\ToStringToMethodCallRector::class)->call('configure', [[
        \Rector\Transform\Rector\String_\ToStringToMethodCallRector::METHOD_NAMES_BY_TYPE => [
            \Symfony\Component\Config\ConfigCache::class => 'getPath',
        ],
    ]]);
};
