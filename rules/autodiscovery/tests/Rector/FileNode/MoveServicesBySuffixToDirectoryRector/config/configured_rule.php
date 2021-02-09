<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Autodiscovery\Rector\FileNode\MoveServicesBySuffixToDirectoryRector::class)->call(
        'configure',
        [[
            \Rector\Autodiscovery\Rector\FileNode\MoveServicesBySuffixToDirectoryRector::GROUP_NAMES_BY_SUFFIX => [
                'Repository',
                'Command',
                'Mapper',
                'Controller',
            ],
        ]]
    );
};
