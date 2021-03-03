<?php

declare(strict_types=1);

use Rector\Doctrine\Rector\ClassMethod\ChangeGetIdTypeToUuidRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeGetIdTypeToUuidRector::class);
};
