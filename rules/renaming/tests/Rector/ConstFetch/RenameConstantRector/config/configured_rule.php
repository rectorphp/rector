<?php

use Rector\Renaming\Rector\ConstFetch\RenameConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameConstantRector::class)->call('configure', [[
        RenameConstantRector::OLD_TO_NEW_CONSTANTS => [
            'MYSQL_ASSOC' => 'MYSQLI_ASSOC',
            'OLD_CONSTANT' => 'NEW_CONSTANT',
        ],
    ]]);
};
