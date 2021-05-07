<?php

use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(SetList::MYSQL_TO_MYSQLI);
    $services = $containerConfigurator->services();
    $services->set(RenameFunctionRector::class)
        ->call('configure', [[
            RenameFunctionRector::OLD_FUNCTION_TO_NEW_FUNCTION => [
                'view' => 'Laravel\Templating\render',
                'sprintf' => 'Safe\sprintf',
            ],
        ]]);
};
