<?php

declare(strict_types=1);

use Rector\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddAllowDynamicPropertiesAttributeRector::class)
        ->configure(['*\Fixture\Process\*']);
};
