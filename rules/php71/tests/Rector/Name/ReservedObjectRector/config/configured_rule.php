<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Php71\Rector\Name\ReservedObjectRector::class)->call('configure', [[
        \Rector\Php71\Rector\Name\ReservedObjectRector::RESERVED_KEYWORDS_TO_REPLACEMENTS => [
            'ReservedObject' => 'SmartObject',
            'Object' => 'AnotherSmartObject',
        ],
    ]]);
};
