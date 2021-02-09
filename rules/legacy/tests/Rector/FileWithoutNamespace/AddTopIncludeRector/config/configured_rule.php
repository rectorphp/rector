<?php

use Rector\Legacy\Rector\FileWithoutNamespace\AddTopIncludeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddTopIncludeRector::class)->call('configure', [[
        AddTopIncludeRector::AUTOLOAD_FILE_PATH => '/../autoloader.php',
    ]]);
};
