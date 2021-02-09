<?php

use Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(MethodCallToPropertyFetchRector::class)->call('configure', [[
        MethodCallToPropertyFetchRector::METHOD_CALL_TO_PROPERTY_FETCHES => [
            'getEntityManager' => 'entityManager',
        ],
    ]]);
};
