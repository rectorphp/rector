<?php

use Rector\Autodiscovery\Rector\FileNode\MoveServicesBySuffixToDirectoryRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(MoveServicesBySuffixToDirectoryRector::class)->call(
        'configure',
        [[
            MoveServicesBySuffixToDirectoryRector::GROUP_NAMES_BY_SUFFIX => [
                'Repository',
                'Command',
                'Mapper',
                'Controller',
            ],
        ]]
    );
};
