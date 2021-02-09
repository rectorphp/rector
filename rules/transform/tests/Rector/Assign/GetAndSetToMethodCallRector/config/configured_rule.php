<?php

use Rector\Transform\Rector\Assign\GetAndSetToMethodCallRector;
use Rector\Transform\Tests\Rector\Assign\GetAndSetToMethodCallRector\Source\Klarka;
use Rector\Transform\Tests\Rector\Assign\GetAndSetToMethodCallRector\Source\SomeContainer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(GetAndSetToMethodCallRector::class)->call('configure', [[
        GetAndSetToMethodCallRector::TYPE_TO_METHOD_CALLS => [
            SomeContainer::class => [
                'get' => 'getService',
                'set' => 'addService',
            ],
            Klarka::class => [
                'get' => 'get',
            ],
        ],
    ]]);
};
