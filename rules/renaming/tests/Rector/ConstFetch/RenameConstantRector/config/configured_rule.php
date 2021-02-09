<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\ConstFetch\RenameConstantRector::class)->call('configure', [[
        \Rector\Renaming\Rector\ConstFetch\RenameConstantRector::OLD_TO_NEW_CONSTANTS => [
            'MYSQL_ASSOC' => 'MYSQLI_ASSOC',
            'OLD_CONSTANT' => 'NEW_CONSTANT',
        ],
    ]]);
};
