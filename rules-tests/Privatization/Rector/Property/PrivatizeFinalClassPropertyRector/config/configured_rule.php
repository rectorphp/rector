<?php

declare(strict_types=1);

use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PrivatizeFinalClassPropertyRector::class);
};
