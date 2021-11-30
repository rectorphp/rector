<?php

declare(strict_types=1);

use Rector\Renaming\Rector\String_\RenameStringRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameStringRector::class)
        ->configure([
            'ROLE_PREVIOUS_ADMIN' => 'IS_IMPERSONATOR',
        ]);
};
