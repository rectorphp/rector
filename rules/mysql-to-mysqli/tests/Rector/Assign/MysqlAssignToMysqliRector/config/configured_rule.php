<?php

declare(strict_types=1);

use Rector\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MysqlAssignToMysqliRector::class);
};
