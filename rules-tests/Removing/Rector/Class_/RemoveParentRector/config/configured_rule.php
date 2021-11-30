<?php

declare(strict_types=1);

use Rector\Removing\Rector\Class_\RemoveParentRector;
use Rector\Tests\Removing\Rector\Class_\RemoveParentRector\Source\ParentTypeToBeRemoved;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveParentRector::class)
        ->configure([ParentTypeToBeRemoved::class]);
};
