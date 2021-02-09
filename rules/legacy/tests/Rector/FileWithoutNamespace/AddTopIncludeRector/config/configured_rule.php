<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Legacy\Rector\FileWithoutNamespace\AddTopIncludeRector::class)->call('configure', [[
        \Rector\Legacy\Rector\FileWithoutNamespace\AddTopIncludeRector::AUTOLOAD_FILE_PATH => '/../autoloader.php',
    ]]);
};
