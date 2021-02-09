<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector::class)->call('configure', [[
        \Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector::METHOD_CALL_TO_PROPERTY_FETCHES => [
            'getEntityManager' => 'entityManager',
        ],
    ]]);
};
