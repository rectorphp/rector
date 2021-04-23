<?php

declare(strict_types=1);

use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(VarToPublicPropertyRector::class);
};
