<?php

declare(strict_types=1);

use Rector\Php74\Rector\FuncCall\GetCalledClassToSelfClassRector;
use Rector\Php74\Rector\FuncCall\GetCalledClassToStaticClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(GetCalledClassToSelfClassRector::class);
    $services->set(GetCalledClassToStaticClassRector::class);
};
