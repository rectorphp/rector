<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Restoration\Rector\New_\CompleteMissingDependencyInNewRector::class)->call('configure', [[
        \Rector\Restoration\Rector\New_\CompleteMissingDependencyInNewRector::CLASS_TO_INSTANTIATE_BY_TYPE => [
            \Rector\Restoration\Tests\Rector\New_\CompleteMissingDependencyInNewRector\Source\RandomDependency::class => \Rector\Restoration\Tests\Rector\New_\CompleteMissingDependencyInNewRector\Source\RandomDependency::class,
        ],
    ]]);
};
