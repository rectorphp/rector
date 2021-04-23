<?php

declare(strict_types=1);

use Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveReferenceFromCallRector::class);
};
