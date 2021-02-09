<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\String_\RenameStringRector::class)->call('configure', [[
        \Rector\Renaming\Rector\String_\RenameStringRector::STRING_CHANGES => [
            'ROLE_PREVIOUS_ADMIN' => 'IS_IMPERSONATOR',
        ],
    ]]);
};
