<?php

use Rector\Removing\Rector\Class_\RemoveTraitRector;
use Rector\Removing\Tests\Rector\Class_\RemoveTraitRector\Source\TraitToBeRemoved;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveTraitRector::class)->call('configure', [[
        RemoveTraitRector::TRAITS_TO_REMOVE => [TraitToBeRemoved::class],
    ]]);
};
