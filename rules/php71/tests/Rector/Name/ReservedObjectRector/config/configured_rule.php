<?php

use Rector\Php71\Rector\Name\ReservedObjectRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ReservedObjectRector::class)->call('configure', [[
        ReservedObjectRector::RESERVED_KEYWORDS_TO_REPLACEMENTS => [
            'ReservedObject' => 'SmartObject',
            'Object' => 'AnotherSmartObject',
        ],
    ]]);
};
