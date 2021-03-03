<?php

declare(strict_types=1);

use Rector\Symfony4\Rector\New_\StringToArrayArgumentProcessRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StringToArrayArgumentProcessRector::class);
};
