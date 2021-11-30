<?php

declare(strict_types=1);

use Rector\Php71\Rector\Name\ReservedObjectRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ReservedObjectRector::class)
        ->configure([
            'ReservedObject' => 'SmartObject',
            'Object' => 'AnotherSmartObject',
        ]);
};
