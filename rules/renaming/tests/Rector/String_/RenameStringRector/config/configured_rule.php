<?php

use Rector\Renaming\Rector\String_\RenameStringRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameStringRector::class)->call('configure', [[
        RenameStringRector::STRING_CHANGES => [
            'ROLE_PREVIOUS_ADMIN' => 'IS_IMPERSONATOR',
        ],
    ]]);
};
